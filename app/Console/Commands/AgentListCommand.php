<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use LLM\Agents\Agent\AgentRegistryInterface;
use LLM\Agents\Solution\ToolLink;

final class AgentListCommand extends Command
{
    protected $signature = 'agent:list';
    protected $description = 'List all available agents.';

    public function handle(AgentRegistryInterface $agents): void
    {
        $this->info('Available agents:');

        $rows = [];
        foreach ($agents->all() as $agent) {
            $tools = \array_map(static fn(ToolLink $tool): string => '- ' . $tool->getName(), $agent->getTools());
            $rows[] = [
                $agent->getKey() . PHP_EOL . '- ' . $agent->getModel()->name,
                \implode(PHP_EOL, $tools),
                \wordwrap($agent->getDescription(), 50, "\n", true),
            ];
        }

        $this->table(['Agent', 'Tools', 'Description'], $rows);
    }
}
