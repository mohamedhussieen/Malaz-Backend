<?php

namespace App\Support;

class MediaUrl
{
    public static function toUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        return url('/storage/'.ltrim($path, '/'));
    }
}
