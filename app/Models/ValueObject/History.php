<?php

declare(strict_types=1);

namespace App\Models\ValueObject;

use LLM\Agents\LLM\Prompt\Chat\Prompt;
use Traversable;

final class History implements \IteratorAggregate, \JsonSerializable
{
    public static function fromString(string $value): self
    {
        return new self(\json_decode(json: $value, associative: true, flags: \JSON_THROW_ON_ERROR));
    }

    public function __construct(
        public array|\JsonSerializable $data = [],
    ) {}

    public function isEmpty(): bool
    {
        return $this->data === [];
    }

    public function toPrompt(): Prompt
    {
        return Prompt::fromArray($this->data);
    }

    public function jsonSerialize(): array
    {
        return $this->data instanceof \JsonSerializable
            ? $this->data->jsonSerialize()
            : $this->data;
    }

    public function __toString(): string
    {
        return \json_encode($this);
    }

    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->data);
    }
}

