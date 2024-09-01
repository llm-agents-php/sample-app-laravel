<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use LLM\Agents\Agent\AgentRegistryInterface;
use LLM\Agents\Chat\ChatHistoryRepositoryInterface;
use LLM\Agents\Chat\ChatServiceInterface;
use LLM\Agents\Chat\Console\ChatSession;
use LLM\Agents\Tool\ToolRegistryInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Cursor;

final class ChatSessionCommand extends Command
{
    protected $signature = 'chat';
    protected $description = 'Start a new chat session';

    public function handle(
        AgentRegistryInterface $agents,
        ChatServiceInterface $chat,
        ChatHistoryRepositoryInterface $chatHistory,
        ToolRegistryInterface $tools,
    ): void {
        $cursor = new Cursor($this->output);
        $cursor->clearScreen();
        Artisan::call('agent:list');

        $chat = new ChatSession(
            input: $this->input,
            output: $this->output,
            agents: $agents,
            chat: $chat,
            chatHistory: $chatHistory,
            tools: $tools,
        );

        $chat->run(
            accountUuid: Uuid::fromString('00000000-0000-0000-0000-000000000000'),
            binPath: 'artisan',
        );
    }
}
