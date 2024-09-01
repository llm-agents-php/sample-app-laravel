<?php

declare(strict_types=1);

namespace App\Agents;

use LLM\Agents\Tool\Tool;
use LLM\Agents\Tool\ToolLanguage;

/**
 * @template T of object
 * @extends Tool<T>
 */
abstract class PhpTool extends Tool
{
    public function getLanguage(): ToolLanguage
    {
        return ToolLanguage::PHP;
    }
}
