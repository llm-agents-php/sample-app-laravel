<?php

declare(strict_types=1);

namespace App\Event;

use LLM\Agents\Chat\ChatHistoryRepositoryInterface;
use LLM\Agents\Chat\Event\Message;
use LLM\Agents\Chat\Event\MessageChunk;
use LLM\Agents\Chat\Event\Question;
use LLM\Agents\Chat\Event\ToolCall;
use LLM\Agents\Chat\Event\ToolCallResult;
use Illuminate\Events\Dispatcher;

final readonly class ChatEventsListener
{
    public function __construct(
        private ChatHistoryRepositoryInterface $history,
    ) {}

    public function listenToolCall(ToolCall $message): void
    {
        $this->history->addMessage($message->sessionUuid, $message);
    }

    public function listenMessage(Message $message): void
    {
        $this->history->addMessage($message->sessionUuid, $message);
    }

    public function listenMessageChunk(MessageChunk $message): void
    {
        $this->history->addMessage($message->sessionUuid, $message);
    }

    public function listenQuestion(Question $message): void
    {
        $this->history->addMessage($message->sessionUuid, $message);
    }

    public function listenToolCallResult(ToolCallResult $message): void
    {
        $this->history->addMessage($message->sessionUuid, $message);
    }

    public function subscribe(Dispatcher $events): void
    {
        $events->listen(
            ToolCall::class,
            [ChatEventsListener::class, 'listenToolCall']
        );

        $events->listen(
            Message::class,
            [ChatEventsListener::class, 'listenMessage']
        );

        $events->listen(
            MessageChunk::class,
            [ChatEventsListener::class, 'listenMessageChunk']
        );

        $events->listen(
            Question::class,
            [ChatEventsListener::class, 'listenQuestion']
        );

        $events->listen(
            ToolCallResult::class,
            [ChatEventsListener::class, 'listenToolCallResult']
        );
    }
}
