<?php

namespace App\Models\Concerns;

trait HasLocalUrl
{
    /**
     * Return the full URL for a stored path by prepending APP_URL.
     */
    protected static function localGetUrl(?string $value): ?string
    {
        return $value ? rtrim(config('app.url'), '/') . $value : null;
    }

    /**
     * Strip the domain from an incoming URL or path before saving,
     * keeping only the path portion (e.g. /storage/categories/image.jpg).
     */
    protected static function localSetValue(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        return '/' . ltrim((string) (parse_url($value, PHP_URL_PATH) ?? $value), '/');
    }
}
