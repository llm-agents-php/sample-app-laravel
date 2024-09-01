<?php

declare(strict_types=1);

namespace App\Models\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

final class Uuid implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes)
    {
        return \Ramsey\Uuid\Uuid::fromString($value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes)
    {
        return $value->toString();
    }
}
