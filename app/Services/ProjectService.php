<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectImage;
use App\Support\CacheVersion;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;

class ProjectService
{
    public function paginate(int $perPage, int $page): LengthAwarePaginator
    {
        $perPage = min($perPage, 50);
        $version = CacheVersion::get('projects');
        $cacheKey = "public:projects:v{$version}:p{$page}:pp{$perPage}";

        return Cache::remember($cacheKey, 3600, function () use ($perPage, $page) {
            return Project::query()
                ->select(['id', 'name', 'name_ar', 'name_en', 'location', 'location_ar', 'location_en', 'cover_path'])
                ->orderByDesc('created_at')
                ->paginate($perPage, ['*'], 'page', $page);
        });
    }

    public function findWithImages(int $id): Project
    {
        return Project::query()
            ->select([
                'id',
                'name',
                'name_ar',
                'name_en',
                'description',
                'description_ar',
                'description_en',
                'location',
                'location_ar',
                'location_en',
                'cover_path',
            ])
            ->with(['images:id,project_id,path,sort_order'])
            ->findOrFail($id);
    }

    public function create(array $data, ?UploadedFile $cover, MediaService $media): Project
    {
        $data = $this->hydrateLegacyFields($data);

        if ($cover) {
            $data['cover_path'] = $media->store($cover, 'projects/covers');
        }

        $project = Project::create($data);
        CacheVersion::bump('projects');

        return $project;
    }

    public function update(Project $project, array $data, ?UploadedFile $cover, MediaService $media): Project
    {
        $data = $this->hydrateLegacyFields($data);

        if ($cover) {
            $data['cover_path'] = $media->update($cover, 'projects/covers', $project->cover_path);
        }

        $project->update($data);
        CacheVersion::bump('projects');

        return $project;
    }

    public function delete(Project $project, MediaService $media): void
    {
        $media->delete($project->cover_path);
        foreach ($project->images as $image) {
            $media->delete($image->path);
        }

        $project->delete();
        CacheVersion::bump('projects');
    }

    public function addGalleryImage(Project $project, UploadedFile $file, int $sortOrder, MediaService $media): ProjectImage
    {
        $path = $media->store($file, 'projects/galleries');
        $image = $project->images()->create([
            'path' => $path,
            'sort_order' => $sortOrder,
        ]);

        CacheVersion::bump('projects');

        return $image;
    }

    public function updateGalleryImage(ProjectImage $image, ?UploadedFile $file, ?int $sortOrder, MediaService $media): ProjectImage
    {
        $data = [];
        if ($file) {
            $data['path'] = $media->update($file, 'projects/galleries', $image->path);
        }

        if ($sortOrder !== null) {
            $data['sort_order'] = $sortOrder;
        }

        if ($data) {
            $image->update($data);
            CacheVersion::bump('projects');
        }

        return $image;
    }

    public function deleteGalleryImage(ProjectImage $image, MediaService $media): void
    {
        $media->delete($image->path);
        $image->delete();
        CacheVersion::bump('projects');
    }

    private function hydrateLegacyFields(array $data): array
    {
        if (isset($data['name_ar']) || isset($data['name_en'])) {
            $data['name'] = $data['name_en'] ?? $data['name_ar'] ?? $data['name'] ?? '';
        }

        if (isset($data['description_ar']) || isset($data['description_en'])) {
            $data['description'] = $data['description_en'] ?? $data['description_ar'] ?? $data['description'] ?? '';
        }

        if (isset($data['location_ar']) || isset($data['location_en'])) {
            $data['location'] = $data['location_en'] ?? $data['location_ar'] ?? $data['location'] ?? '';
        }

        return $data;
    }
}
