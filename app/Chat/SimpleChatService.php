<?php

declare(strict_types=1);

namespace App\Chat;

use App\Agents\DynamicMemoryTool\DynamicMemoryService;
use App\Models\Session;
use LLM\Agents\Agent\AgentRepositoryInterface;
use LLM\Agents\Agent\Exception\AgentNotFoundException;
use LLM\Agents\Agent\Execution;
use LLM\Agents\Chat\AgentExecutorBuilder;
use LLM\Agents\Chat\ChatServiceInterface;
use LLM\Agents\Chat\SessionInterface;
use LLM\Agents\Chat\StreamChunkCallback;
use LLM\Agents\LLM\Prompt\Chat\Prompt;
use LLM\Agents\LLM\Prompt\Chat\ToolCallResultMessage;
use LLM\Agents\LLM\Response\ChatResponse;
use LLM\Agents\LLM\Response\ToolCall;
use LLM\Agents\LLM\Response\ToolCalledResponse;
use LLM\Agents\PromptGenerator\Context;
use LLM\Agents\Solution\SolutionMetadata;
use LLM\Agents\Tool\ToolExecutor;
use Psr\EventDispatcher\EventDispatcherInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final readonly class SimpleChatService implements ChatServiceInterface
{
    public function __construct(
        private AgentExecutorBuilder $builder,
        private AgentRepositoryInterface $agents,
        private ToolExecutor $toolExecutor,
        private DynamicMemoryService $memoryService,
        private ?EventDispatcherInterface $eventDispatcher = null,
    ) {}

    public function getSession(UuidInterface $sessionUuid): SessionInterface
    {
        return Session::findOrFail($sessionUuid);
    }

    public function startSession(UuidInterface $accountUuid, string $agentName): UuidInterface
    {
        if (!$this->agents->has($agentName)) {
            throw new AgentNotFoundException($agentName);
        }

        $agent = $this->agents->get($agentName);

        $session = Session::create([
            'id' => Uuid::uuid7(),
            'account_uuid' => $accountUuid,
            'agent_name' => $agentName,
            'title' => $agent->getDescription(),
        ]);

        return $session->getUuid();
    }

    public function ask(UuidInterface $sessionUuid, string|\Stringable $message): UuidInterface
    {
        $session = $this->getSession($sessionUuid);

        $prompt = null;
        if (!$session->history->isEmpty()) {
            $prompt = $session->history->toPrompt();
        }

        $messageUuid = Uuid::uuid4();
        $this->eventDispatcher?->dispatch(
            new \LLM\Agents\Chat\Event\Question(
                sessionUuid: $session->getUuid(),
                messageUuid: $messageUuid,
                createdAt: new \DateTimeImmutable(),
                message: $message,
            ),
        );

        $execution = $this->buildAgent(
            session: $session,
            prompt: $prompt,
        )->ask($message);

        $this->handleResult($execution, $session);

        return $messageUuid;
    }

    public function closeSession(UuidInterface $sessionUuid): void
    {
        $session = $this->getSession($sessionUuid);
        $session->finished_at = now();

        $this->updateSession($session);
    }

    public function updateSession(SessionInterface $session): void
    {
        $session->save();
    }

    private function handleResult(Execution $execution, SessionInterface $session): void
    {
        $finished = false;
        while (true) {
            $result = $execution->result;
            $prompt = $execution->prompt;

            if ($result instanceof ToolCalledResponse) {
                // First, call all tools.
                $toolsResponse = [];
                foreach ($result->tools as $tool) {
                    $toolsResponse[] = $this->callTool($session, $tool);
                }

                // Then add the tools responses to the prompt.
                foreach ($toolsResponse as $toolResponse) {
                    $prompt = $prompt->withAddedMessage($toolResponse);
                }

                $execution = $this->buildAgent(
                    session: $session,
                    prompt: $prompt,
                )->continue();
            } elseif ($result instanceof ChatResponse) {
                $finished = true;

                $this->eventDispatcher?->dispatch(
                    new \LLM\Agents\Chat\Event\Message(
                        sessionUuid: $session->getUuid(),
                        createdAt: new \DateTimeImmutable(),
                        message: $result->content,
                    ),
                );
            }

            $session->updateHistory($prompt->toArray());
            $this->updateSession($session);

            if ($finished) {
                break;
            }
        }
    }

    private function buildAgent(SessionInterface $session, ?Prompt $prompt): AgentExecutorBuilder
    {
        $context = new Context();
        $context->setAuthContext([
            'account_uuid' => (string) $session->account_uuid,
            'session_uuid' => (string) $session->getUuid(),
        ]);

        $agent = $this->builder
            ->withAgentKey($session->getAgentName())
            ->withStreamChunkCallback(
                new StreamChunkCallback(
                    sessionUuid: $session->getUuid(),
                    eventDispatcher: $this->eventDispatcher,
                ),
            )
            ->withPromptContext($context);

        if ($prompt === null) {
            return $agent;
        }

        $memories = $this->memoryService->getCurrentMemory($session->getUuid());

        return $agent->withPrompt($prompt->withValues([
            'dynamic_memory' => \implode(
                "\n",
                \array_map(
                    fn(SolutionMetadata $memory) => $memory->content,
                    $memories->memories,
                ),
            ),
        ]));
    }

    private function callTool(SessionInterface $session, ToolCall $tool): ToolCallResultMessage
    {
        $this->eventDispatcher?->dispatch(
            new \LLM\Agents\Chat\Event\ToolCall(
                sessionUuid: $session->getUuid(),
                id: $tool->id,
                tool: $tool->name,
                arguments: $tool->arguments,
                createdAt: new \DateTimeImmutable(),
            ),
        );

        try {
            $functionResult = $this->toolExecutor->execute($tool->name, $tool->arguments);

            $this->eventDispatcher?->dispatch(
                new \LLM\Agents\Chat\Event\ToolCallResult(
                    sessionUuid: $session->getUuid(),
                    id: $tool->id,
                    tool: $tool->name,
                    result: $functionResult,
                    createdAt: new \DateTimeImmutable(),
                ),
            );
        } catch (\Throwable $e) {
            $this->eventDispatcher?->dispatch(
                new \LLM\Agents\Chat\Event\ToolCallResult(
                    sessionUuid: $session->getUuid(),
                    id: $tool->id,
                    tool: $tool->name,
                    result: \json_encode([
                        'error' => $e->getMessage(),
                    ]),
                    createdAt: new \DateTimeImmutable(),
                ),
            );

            return new ToolCallResultMessage(
                id: $tool->id,
                content: [
                    $e->getMessage(),
                ],
            );
        }

        return new ToolCallResultMessage(
            id: $tool->id,
            content: [$functionResult],
        );
    }

    public function getLatestSession(): ?SessionInterface
    {
        return Session::latest()->first();
    }

    public function getLatestSessions(int $limit = 3): array
    {
        return Session::latest()->limit($limit)->get();
    }
}
