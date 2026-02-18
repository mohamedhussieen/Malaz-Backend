<?php

namespace App\Services;

use App\Models\HomeContent;
use App\Models\HomeImage;
use App\Support\CacheVersion;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class HomeService
{
    private ?array $homeContentColumns = null;
    private ?array $homeImageColumns = null;

    public function get(): HomeContent
    {
        $version = CacheVersion::get('home');
        $cacheKey = "public:home:v{$version}";

        return Cache::remember($cacheKey, 3600, function () {
            $home = HomeContent::query()
                ->with(['images:id,home_content_id,name,path,sort_order'])
                ->first();

            if (!$home) {
                $home = HomeContent::create($this->filterDataByHomeContentColumns([
                    'headline_text' => '',
                    'headline_text_ar' => '',
                    'headline_text_en' => '',
                    'body_text' => '',
                    'body_text_ar' => '',
                    'body_text_en' => '',
                ]));

                $home->load(['images:id,home_content_id,name,path,sort_order']);
            }

            return $home;
        });
    }

    public function update(HomeContent $home, array $data): HomeContent
    {
        $data = $this->hydrateLegacyFields($data);
        $data = $this->filterDataByHomeContentColumns($data);
        $home->update($data);
        CacheVersion::bump('home');

        return $home;
    }

    public function addHeroImage(HomeContent $home, UploadedFile $file, ?string $name, int $sortOrder, MediaService $media): HomeImage
    {
        $path = $media->store($file, 'home/hero_gallery');
        $image = $home->images()->create($this->filterDataByHomeImageColumns([
            'name' => $name,
            'path' => $path,
            'sort_order' => $sortOrder,
        ]));

        CacheVersion::bump('home');

        return $image;
    }

    public function updateHeroImage(HomeImage $image, ?UploadedFile $file, ?array $data, MediaService $media): HomeImage
    {
        $updates = $data ?? [];

        if ($file) {
            $updates['path'] = $media->update($file, 'home/hero_gallery', $image->path);
        }

        $updates = $this->filterDataByHomeImageColumns($updates);
        if ($updates !== []) {
            $image->update($updates);
            CacheVersion::bump('home');
        }

        return $image;
    }

    public function deleteHeroImage(HomeImage $image, MediaService $media): void
    {
        $media->delete($image->path);
        $image->delete();
        CacheVersion::bump('home');
    }

    private function hydrateLegacyFields(array $data): array
    {
        if (isset($data['headline_text_ar']) || isset($data['headline_text_en'])) {
            $data['headline_text'] = $data['headline_text_en'] ?? $data['headline_text_ar'] ?? $data['headline_text'] ?? '';
        }

        if (isset($data['body_text_ar']) || isset($data['body_text_en'])) {
            $data['body_text'] = $data['body_text_en'] ?? $data['body_text_ar'] ?? $data['body_text'] ?? '';
        }

        return $data;
    }

    private function homeContentColumns(): array
    {
        if ($this->homeContentColumns !== null) {
            return $this->homeContentColumns;
        }

        $this->homeContentColumns = array_fill_keys(Schema::getColumnListing('home_contents'), true);

        return $this->homeContentColumns;
    }

    private function homeImageColumns(): array
    {
        if ($this->homeImageColumns !== null) {
            return $this->homeImageColumns;
        }

        $this->homeImageColumns = array_fill_keys(Schema::getColumnListing('home_images'), true);

        return $this->homeImageColumns;
    }

    private function filterDataByHomeContentColumns(array $data): array
    {
        return $this->filterDataByKnownColumns($data, $this->homeContentColumns());
    }

    private function filterDataByHomeImageColumns(array $data): array
    {
        return $this->filterDataByKnownColumns($data, $this->homeImageColumns());
    }

    private function filterDataByKnownColumns(array $data, array $columns): array
    {
        return array_filter(
            $data,
            static fn (mixed $value, string $key): bool => isset($columns[$key]),
            ARRAY_FILTER_USE_BOTH
        );
    }
}
