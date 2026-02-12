<?php

namespace App\Services;

use App\Models\PlatformLink;
use App\Support\CacheVersion;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class PlatformLinkService
{
    public function publicList(): Collection
    {
        $version = CacheVersion::get('platforms');
        $cacheKey = "public:platforms:v{$version}";

        return Cache::remember($cacheKey, 3600, function () {
            return PlatformLink::query()
                ->select(['id', 'key', 'url', 'is_active'])
                ->where('is_active', true)
                ->orderBy('id')
                ->get();
        });
    }

    public function adminList(): Collection
    {
        return PlatformLink::query()
            ->select(['id', 'key', 'url', 'is_active'])
            ->orderBy('id')
            ->get();
    }

    public function upsert(string $key, array $data): PlatformLink
    {
        $platformLink = PlatformLink::updateOrCreate(['key' => $key], $data);
        CacheVersion::bump('platforms');

        return $platformLink;
    }

    public function toggle(PlatformLink $platformLink): PlatformLink
    {
        $platformLink->update(['is_active' => !$platformLink->is_active]);
        CacheVersion::bump('platforms');

        return $platformLink;
    }
}
