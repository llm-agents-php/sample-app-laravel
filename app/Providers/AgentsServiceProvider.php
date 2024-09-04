<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use LLM\Agents\Agent\AgentRegistry;
use LLM\Agents\Agent\AgentRegistryInterface;
use LLM\Agents\Agent\AgentRepositoryInterface;
use LLM\Agents\AgentExecutor\ExecutorInterface;
use LLM\Agents\AgentExecutor\ExecutorPipeline;
use LLM\Agents\AgentExecutor\Interceptor\GeneratePromptInterceptor;
use LLM\Agents\AgentExecutor\Interceptor\InjectModelInterceptor;
use LLM\Agents\AgentExecutor\Interceptor\InjectOptionsInterceptor;
use LLM\Agents\AgentExecutor\Interceptor\InjectResponseIntoPromptInterceptor;
use LLM\Agents\AgentExecutor\Interceptor\InjectToolsInterceptor;
use LLM\Agents\JsonSchema\Mapper\SchemaMapper;
use LLM\Agents\LLM\ContextFactoryInterface;
use LLM\Agents\LLM\OptionsFactoryInterface;
use LLM\Agents\OpenAI\Client\ContextFactory;
use LLM\Agents\OpenAI\Client\OptionsFactory;
use LLM\Agents\Tool\SchemaMapperInterface;
use LLM\Agents\Tool\ToolRegistry;
use LLM\Agents\Tool\ToolRegistryInterface;
use LLM\Agents\Tool\ToolRepositoryInterface;

final class AgentsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ToolRegistry::class, ToolRegistry::class);
        $this->app->singleton(ToolRegistryInterface::class, ToolRegistry::class);
        $this->app->singleton(ToolRepositoryInterface::class, ToolRegistry::class);

        $this->app->singleton(AgentRegistry::class, AgentRegistry::class);
        $this->app->singleton(AgentRegistryInterface::class, AgentRegistry::class);
        $this->app->singleton(AgentRepositoryInterface::class, AgentRegistry::class);

        $this->app->singleton(OptionsFactoryInterface::class, OptionsFactory::class);
        $this->app->singleton(ContextFactoryInterface::class, ContextFactory::class);

        $this->app->singleton(SchemaMapperInterface::class, SchemaMapper::class);

        $this->app->singleton(ExecutorInterface::class, static function (
            Application $app,
        ) {
            return $app->make(ExecutorPipeline::class)->withInterceptor(
                $app->make(GeneratePromptInterceptor::class),
                $app->make(InjectModelInterceptor::class),
                $app->make(InjectToolsInterceptor::class),
                $app->make(InjectOptionsInterceptor::class),
                $app->make(InjectResponseIntoPromptInterceptor::class),
            );
        });
    }

    public function boot(
        AgentRegistryInterface $agents,
        ToolRegistryInterface $tools,
    ): void {
        foreach (config('agents.agents') as $agent) {
            $agents->register($this->app->make($agent)->create());
        }

        foreach (config('agents.tools') as $tool) {
            $tools->register($this->app->make($tool));
        }
    }
}
