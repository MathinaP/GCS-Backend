<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * PostgreSQL boolean cast compatible with PDO::ATTR_EMULATE_PREPARES.
 * Emulated prepares send PHP `true` as integer 1, which Postgres rejects
 * for boolean columns. Writing the string 'true'/'false' avoids the mismatch.
 */
class PgBoolean implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        return $value ? 'true' : 'false';
    }
}
