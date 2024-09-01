<?php

declare(strict_types=1);

namespace App\Agents\DynamicMemoryTool;

use LLM\Agents\Solution\SolutionMetadata;
use Psr\SimpleCache\CacheInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final readonly class DynamicMemoryService
{
    public function __construct(
        private CacheInterface $cache,
    ) {}

    public function addMemory(UuidInterface $sessionUuid, SolutionMetadata $metadata): void
    {
        $memories = $this->getCurrentMemory($sessionUuid);

        $memories->addMemory($metadata);

        $this->cache->set($this->getKey($sessionUuid), $memories);
    }

    public function updateMemory(UuidInterface $sessionUuid, SolutionMetadata $metadata): void
    {
        $memories = $this->getCurrentMemory($sessionUuid);
        $memories->updateMemory($metadata);

        $this->cache->set($this->getKey($sessionUuid), $memories);
    }

    public function getCurrentMemory(UuidInterface $sessionUuid): Memories
    {
        return $this->cache->get($this->getKey($sessionUuid)) ?? new Memories(
            Uuid::uuid4(),
        );
    }

    private function getKey(UuidInterface $sessionUuid): string
    {
        return 'user_memory';
    }
}
