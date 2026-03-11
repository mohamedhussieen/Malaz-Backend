<?php

namespace App\Support;

class MediaUrl
{
    public static function toUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return url('/storage/'.ltrim($path, '/'));
    }
}
