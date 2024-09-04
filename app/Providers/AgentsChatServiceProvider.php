<?php

declare(strict_types=1);

namespace App\Providers;

use App\Chat\ChatHistoryRepository;
use App\Chat\SimpleChatService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use LLM\Agents\Chat\ChatHistoryRepositoryInterface;
use LLM\Agents\Chat\ChatServiceInterface;
use LLM\Agents\PromptGenerator\Interceptors\AgentMemoryInjector;
use LLM\Agents\PromptGenerator\Interceptors\InstructionGenerator;
use LLM\Agents\PromptGenerator\Interceptors\LinkedAgentsInjector;
use LLM\Agents\PromptGenerator\Interceptors\SessionContextInjector;
use LLM\Agents\PromptGenerator\Interceptors\UserPromptInjector;
use LLM\Agents\PromptGenerator\PromptGeneratorPipeline;

final class AgentsChatServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ChatServiceInterface::class, SimpleChatService::class);

        // Register ChatHistoryRepositoryInterface here
        $this->app->singleton(
            ChatHistoryRepositoryInterface::class,
            static function (Application $app) {
                return new ChatHistoryRepository($app->make('cache.store'));
            },
        );

        $this->app->singleton(PromptGeneratorPipeline::class, static function (
            Application $app,
        ): PromptGeneratorPipeline {
            $pipeline = new PromptGeneratorPipeline();

            return $pipeline->withInterceptor(
                new InstructionGenerator(),
                new AgentMemoryInjector(),
                $app->make(LinkedAgentsInjector::class),
                new SessionContextInjector(),
                new UserPromptInjector(),
            );
        });
    }
}
