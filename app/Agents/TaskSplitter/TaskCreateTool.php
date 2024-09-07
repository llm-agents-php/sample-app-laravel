<?php

declare(strict_types=1);

namespace App\Agents\TaskSplitter;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Storage;
use LLM\Agents\Tool\PhpTool;
use Ramsey\Uuid\Uuid;

/**
 * @extends PhpTool<TaskCreateInput>
 */
final class TaskCreateTool extends PhpTool
{
    public const NAME = 'create_task';

    public function __construct(
        private readonly Application $app,
    ) {
        parent::__construct(
            name: self::NAME,
            inputSchema: TaskCreateInput::class,
            description: 'Create a task or subtask in the task management system.',
        );
    }

    public function execute(object $input): string
    {
        $uuid = (string) Uuid::uuid4();
        $dir = $this->app->storagePath('tasks/');

        if ($input->parentTaskUuid !== '') {
            $path = \sprintf('%s/%s/%s.json', $input->projectUuid, $input->parentTaskUuid, $uuid);
        } else {
            $path = \sprintf('%s/%s.json', $input->projectUuid, $uuid);
        }

        $fullPath = $dir . $path;

        Storage::createDirectory(\dirname($fullPath));
        Storage::write(
            $fullPath,
            \sprintf(
                <<<'CONTENT'
uuid: %s
parent_uuid: %s
project_uuid: %s

---

## %s

%s
CONTENT,
                $uuid,
                $input->parentTaskUuid ?? '-',
                $input->projectUuid,
                $input->name,
                \trim($input->description),
            ),
        );

        return json_encode(['task_uuid' => $uuid]);
    }
}
