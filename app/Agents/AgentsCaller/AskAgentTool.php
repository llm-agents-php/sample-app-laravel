<?php

declare(strict_types=1);

namespace App\Agents\AgentsCaller;

use App\Agents\PhpTool;
use LLM\Agents\Agent\AgentExecutor;
use LLM\Agents\LLM\Prompt\Chat\ToolCallResultMessage;
use LLM\Agents\LLM\Response\ToolCalledResponse;
use LLM\Agents\Tool\ToolExecutor;

/**
 * @extends PhpTool<AskAgentInput>
 */
final class AskAgentTool extends PhpTool
{
    public const NAME = 'ask_agent';

    public function __construct(
        private readonly AgentExecutor $executor,
        private readonly ToolExecutor $toolExecutor,
    ) {
        parent::__construct(
            name: self::NAME,
            inputSchema: AskAgentInput::class,
            description: 'Ask an agent with given name to execute a task.',
        );
    }

    public function execute(object $input): string|\Stringable
    {
        $prompt = \sprintf(
            <<<'PROMPT'
%s
Important rules:
- Think before responding to the user.
- Don not markup the content. Only JSON is allowed.
- Don't write anything except the answer using JSON schema.
- Answer in JSON using this schema:
%s
PROMPT
            ,
            $input->question,
            $input->outputSchema,
        );

        // TODO: make async
        while (true) {
            $execution = $this->executor->execute($input->name, $prompt);
            $result = $execution->result;
            $prompt = $execution->prompt;

            if ($result instanceof ToolCalledResponse) {
                foreach ($result->tools as $tool) {
                    $functionResult = $this->toolExecutor->execute($tool->name, $tool->arguments);

                    $prompt = $prompt->withAddedMessage(
                        new ToolCallResultMessage(
                            id: $tool->id,
                            content: [$functionResult],
                        ),
                    );
                }

                continue;
            }

            break;
        }

        return \json_encode($result->content);
    }
}