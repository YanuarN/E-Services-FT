<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait HasPublicToken
{
    protected static function bootHasPublicToken(): void
    {
        static::creating(function (Model $model): void {
            if (filled($model->getAttribute('public_token'))) {
                return;
            }

            $model->setAttribute('public_token', static::generateUniquePublicToken());
        });
    }

    public static function generateUniquePublicToken(): string
    {
        do {
            $token = Str::upper(Str::random(16));
        } while (static::query()->where('public_token', $token)->exists());

        return $token;
    }
}
