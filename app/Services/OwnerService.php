<?php

namespace App\Services;

use App\Models\Owner;
use App\Support\CacheVersion;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;

class OwnerService
{
    public function paginate(int $perPage, int $page, ?string $search = null): LengthAwarePaginator
    {
        $perPage = min($perPage, 50);
        $search = is_string($search) ? trim($search) : '';

        if ($search !== '') {
            return Owner::query()
                ->select([
                    'id',
                    'name',
                    'name_ar',
                    'name_en',
                    'title',
                    'title_ar',
                    'title_en',
                    'bio',
                    'bio_ar',
                    'bio_en',
                    'avatar_path',
                ])
                ->where(function ($query) use ($search) {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('name_ar', 'like', "%{$search}%")
                        ->orWhere('name_en', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%")
                        ->orWhere('title_ar', 'like', "%{$search}%")
                        ->orWhere('title_en', 'like', "%{$search}%");
                })
                ->paginate($perPage, ['*'], 'page', $page);
        }

        $version = CacheVersion::get('owners');
        $cacheKey = "public:owners:v{$version}:p{$page}:pp{$perPage}";

        return Cache::remember($cacheKey, 3600, function () use ($perPage, $page) {
            return Owner::query()
                ->select([
                    'id',
                    'name',
                    'name_ar',
                    'name_en',
                    'title',
                    'title_ar',
                    'title_en',
                    'bio',
                    'bio_ar',
                    'bio_en',
                    'avatar_path',
                ])
                ->paginate($perPage, ['*'], 'page', $page);
        });
    }

    public function create(array $data, ?UploadedFile $avatar, MediaService $media): Owner
    {
        $data = $this->hydrateLegacyFields($data);

        if ($avatar) {
            $data['avatar_path'] = $media->store($avatar, 'owners/avatars');
        }

        $owner = Owner::create($data);
        CacheVersion::bump('owners');

        return $owner;
    }

    public function update(Owner $owner, array $data, ?UploadedFile $avatar, MediaService $media): Owner
    {
        $data = $this->hydrateLegacyFields($data);

        if ($avatar) {
            $data['avatar_path'] = $media->update($avatar, 'owners/avatars', $owner->avatar_path);
        }

        $owner->update($data);
        CacheVersion::bump('owners');

        return $owner;
    }

    public function delete(Owner $owner, MediaService $media): void
    {
        $media->delete($owner->avatar_path);
        $owner->delete();
        CacheVersion::bump('owners');
    }

    private function hydrateLegacyFields(array $data): array
    {
        if (isset($data['name_ar']) || isset($data['name_en'])) {
            $data['name'] = $data['name_en'] ?? $data['name_ar'] ?? $data['name'] ?? '';
        }

        if (array_key_exists('title_ar', $data) || array_key_exists('title_en', $data)) {
            $data['title'] = $data['title_en'] ?? $data['title_ar'] ?? null;
        }

        if (array_key_exists('bio_ar', $data) || array_key_exists('bio_en', $data)) {
            $data['bio'] = $data['bio_en'] ?? $data['bio_ar'] ?? null;
        }

        return $data;
    }
}
