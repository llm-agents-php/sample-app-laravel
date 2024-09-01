<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use LLM\Agents\Chat\ChatHistoryRepositoryInterface;
use LLM\Agents\Chat\ChatServiceInterface;
use LLM\Agents\Chat\Console\ChatHistory;
use Ramsey\Uuid\Uuid;

final class ChatWindowCommand extends Command
{
    protected $signature = 'chat:session {session_uuid}';
    protected $description = 'Chat session';

    public function handle(
        ChatHistoryRepositoryInterface $chatHistory,
        ChatServiceInterface $chatService,
    ): void {
        $chatWindow = new ChatHistory(
            input: $this->input,
            output: $this->output,
            chatHistory: $chatHistory,
            chat: $chatService,
        );

        $chatWindow->run(Uuid::fromString($this->argument('session_uuid')));
    }
}
