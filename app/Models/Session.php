<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Casts\History;
use App\Models\Casts\Uuid;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use LLM\Agents\Chat\SessionInterface;
use Ramsey\Uuid\UuidInterface;

/**
 * @property UuidInterface $account_uuid
 * @property string $agent_name
 * @property ValueObject\History $history
 * @property \DateTimeInterface $finished_at
 * @property-read UuidInterface $uuid
 * @property-read string $title
 * @property-read bool $is_finished
 */
final class Session extends Model implements SessionInterface
{
    use SoftDeletes, HasUuids;

    protected $table = 'chat_sessions';
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'id' => Uuid::class,
            'account_uuid' => Uuid::class,
            'finished_at' => 'datetime',
            'history' => History::class,
        ];
    }

    public function getUuid(): UuidInterface
    {
        return $this->getKey();
    }

    public function getAgentName(): string
    {
        return $this->agent_name;
    }

    public function updateHistory(array $messages): void
    {
        $this->history = new ValueObject\History($messages);
    }

    public function isFinished(): bool
    {
        return $this->trashed();
    }

    public function setDescription(string $description): void
    {
        $this->title = $description;
    }

    public function getDescription(): ?string
    {
        return $this->title;
    }
}
