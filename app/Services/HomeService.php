<?php

namespace App\Services;

use App\Models\HomeContent;
use App\Models\HomeImage;
use App\Support\CacheVersion;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;

class HomeService
{
    public function get(): HomeContent
    {
        $version = CacheVersion::get('home');
        $cacheKey = "public:home:v{$version}";

        return Cache::remember($cacheKey, 3600, function () {
            $home = HomeContent::query()
                ->with(['images:id,home_content_id,path,sort_order'])
                ->first();

            if (!$home) {
                $home = HomeContent::create([
                    'headline_text' => '',
                    'headline_text_ar' => '',
                    'headline_text_en' => '',
                    'body_text' => '',
                    'body_text_ar' => '',
                    'body_text_en' => '',
                ]);

                $home->load(['images:id,home_content_id,path,sort_order']);
            }

            return $home;
        });
    }

    public function update(HomeContent $home, array $data): HomeContent
    {
        $data = $this->hydrateLegacyFields($data);
        $home->update($data);
        CacheVersion::bump('home');

        return $home;
    }

    public function addHeroImage(HomeContent $home, UploadedFile $file, int $sortOrder, MediaService $media): HomeImage
    {
        $path = $media->store($file, 'home/hero_gallery');
        $image = $home->images()->create([
            'path' => $path,
            'sort_order' => $sortOrder,
        ]);

        CacheVersion::bump('home');

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
}
