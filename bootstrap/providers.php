<?php

return [
    LLM\Agents\JsonSchema\Mapper\Integration\Laravel\SchemaMapperServiceProvider::class,
    App\Providers\AppServiceProvider::class,
    App\Providers\SmartHomeServiceProvider::class,
    App\Providers\AgentsServiceProvider::class,
    App\Providers\AgentsChatServiceProvider::class,
    LLM\Agents\Agent\SymfonyConsole\Integrations\Laravel\SymfonyConsoleServiceProvider::class,
];
