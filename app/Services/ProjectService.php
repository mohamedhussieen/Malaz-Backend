<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectImage;
use App\Support\CacheVersion;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class ProjectService
{
    private ?array $projectColumns = null;
    private ?array $projectImageColumns = null;

    public function paginate(int $perPage, int $page, ?string $search = null): LengthAwarePaginator
    {
        $perPage = min($perPage, 50);
        $search = is_string($search) ? trim($search) : '';
        $selectColumns = $this->projectListSelectColumns();
        $searchColumns = $this->availableProjectColumns([
            'name',
            'name_ar',
            'name_en',
            'location',
            'location_ar',
            'location_en',
            'description',
            'description_ar',
            'description_en',
        ]);

        if ($search !== '') {
            $query = Project::query()->select($selectColumns);

            if ($searchColumns !== []) {
                $query->where(function ($builder) use ($search, $searchColumns) {
                    foreach ($searchColumns as $index => $column) {
                        if ($index === 0) {
                            $builder->where($column, 'like', "%{$search}%");
                            continue;
                        }

                        $builder->orWhere($column, 'like', "%{$search}%");
                    }
                });
            }

            return $query
                ->orderByDesc('created_at')
                ->paginate($perPage, ['*'], 'page', $page);
        }

        $version = CacheVersion::get('projects');
        $cacheKey = "public:projects:v{$version}:p{$page}:pp{$perPage}";

        return Cache::remember($cacheKey, 3600, function () use ($perPage, $page, $selectColumns) {
            return Project::query()
                ->select($selectColumns)
                ->orderByDesc('created_at')
                ->paginate($perPage, ['*'], 'page', $page);
        });
    }

    public function findWithImages(int $id): Project
    {
        $selectColumns = $this->availableProjectColumns([
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
            'is_featured_home',
        ]);

        return Project::query()
            ->select($selectColumns)
            ->with(['images:id,project_id,name,path,sort_order'])
            ->findOrFail($id);
    }

    public function create(array $data, ?UploadedFile $cover, MediaService $media): Project
    {
        $data = $this->hydrateLegacyFields($data);

        if ($cover) {
            $data['cover_path'] = $media->store($cover, 'projects/covers');
        }

        $data = $this->filterDataByProjectColumns($data);
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

        $data = $this->filterDataByProjectColumns($data);
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

    public function addGalleryImage(
        Project $project,
        UploadedFile $file,
        ?string $name,
        int $sortOrder,
        MediaService $media
    ): ProjectImage
    {
        $path = $media->store($file, 'projects/galleries');
        $imageData = $this->filterDataByProjectImageColumns([
            'name' => $name,
            'path' => $path,
            'sort_order' => $sortOrder,
        ]);
        $image = $project->images()->create($imageData);

        CacheVersion::bump('projects');

        return $image;
    }

    public function updateGalleryImage(
        ProjectImage $image,
        ?UploadedFile $file,
        ?array $data,
        MediaService $media
    ): ProjectImage {
        $updates = $data ?? [];

        if ($file) {
            $updates['path'] = $media->update($file, 'projects/galleries', $image->path);
        }

        $updates = $this->filterDataByProjectImageColumns($updates);
        if ($updates !== []) {
            $image->update($updates);
            CacheVersion::bump('projects');
        }

        return $image;
    }

    public function featuredForHome(int $limit = 8): Collection
    {
        if (!$this->hasProjectColumn('is_featured_home')) {
            return new Collection();
        }

        $limit = max(1, min($limit, 30));
        $version = CacheVersion::get('projects');
        $cacheKey = "public:home:featured-projects:v{$version}:l{$limit}";
        $selectColumns = $this->projectListSelectColumns();

        return Cache::remember($cacheKey, 3600, function () use ($limit, $selectColumns) {
            return Project::query()
                ->select($selectColumns)
                ->where('is_featured_home', true)
                ->orderByDesc('updated_at')
                ->limit($limit)
                ->get();
        });
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

    private function projectListSelectColumns(): array
    {
        return $this->availableProjectColumns([
            'id',
            'name',
            'name_ar',
            'name_en',
            'location',
            'location_ar',
            'location_en',
            'cover_path',
            'is_featured_home',
        ]);
    }

    private function availableProjectColumns(array $columns): array
    {
        $availableColumns = $this->projectColumns();

        return array_values(array_filter($columns, static fn (string $column): bool => isset($availableColumns[$column])));
    }

    private function hasProjectColumn(string $column): bool
    {
        $availableColumns = $this->projectColumns();

        return isset($availableColumns[$column]);
    }

    private function projectColumns(): array
    {
        if ($this->projectColumns !== null) {
            return $this->projectColumns;
        }

        $this->projectColumns = array_fill_keys(Schema::getColumnListing('projects'), true);

        return $this->projectColumns;
    }

    private function projectImageColumns(): array
    {
        if ($this->projectImageColumns !== null) {
            return $this->projectImageColumns;
        }

        $this->projectImageColumns = array_fill_keys(Schema::getColumnListing('project_images'), true);

        return $this->projectImageColumns;
    }

    private function filterDataByProjectColumns(array $data): array
    {
        return $this->filterDataByKnownColumns($data, $this->projectColumns());
    }

    private function filterDataByProjectImageColumns(array $data): array
    {
        return $this->filterDataByKnownColumns($data, $this->projectImageColumns());
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
