<?php

declare(strict_types=1);

namespace App\Models\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

final class History implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): \App\Models\ValueObject\History
    {
        if ($value === null) {
            return new \App\Models\ValueObject\History([]);
        }

        return \App\Models\ValueObject\History::fromString($value);
    }

    /**
     * @param \App\Models\ValueObject\History $value
     */
    public function set(Model $model, string $key, mixed $value, array $attributes)
    {
        return (string) $value;
    }
}
