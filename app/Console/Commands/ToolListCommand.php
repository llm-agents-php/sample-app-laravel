<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use LLM\Agents\Tool\ToolRegistryInterface;

final class ToolListCommand extends Command
{
    protected $signature = 'tool:list';
    protected $description = 'List all available tools.';

    public function handle(ToolRegistryInterface $tools): void
    {
        $rows = [];
        foreach ($tools->all() as $tool) {
            $rows[] = [$tool->getName(), $tool->getDescription()];
        }

        $this->table(['Tool', 'Description'], $rows);
    }
}
