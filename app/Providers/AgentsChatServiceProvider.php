<?php

declare(strict_types=1);

namespace App\Providers;

use App\Chat\ChatHistoryRepository;
use App\Chat\SimpleChatService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use LLM\Agents\Chat\AgentPromptGenerator;
use LLM\Agents\Chat\ChatHistoryRepositoryInterface;
use LLM\Agents\Chat\ChatServiceInterface;
use LLM\Agents\LLM\AgentPromptGeneratorInterface;

final class AgentsChatServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ChatServiceInterface::class, SimpleChatService::class);
        $this->app->singleton(AgentPromptGeneratorInterface::class, AgentPromptGenerator::class);

        // Register ChatHistoryRepositoryInterface here
        $this->app->singleton(
            ChatHistoryRepositoryInterface::class,
            static function (Application $app) {
                return new ChatHistoryRepository($app->make('cache.store'));
            },
        );
    }
}
