<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class CacheVersion
{
    public static function get(string $key): int
    {
        return Cache::get(self::key($key), 1);
    }

    public static function bump(string $key): void
    {
        $cacheKey = self::key($key);
        if (Cache::has($cacheKey)) {
            Cache::increment($cacheKey);
        } else {
            Cache::put($cacheKey, 2);
        }
    }

    private static function key(string $key): string
    {
        return 'cache_version:' . $key;
    }
}
