<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectImage;
use App\Support\CacheVersion;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProjectService
{
    private ?array $projectColumns = null;
    private ?array $projectImageColumns = null;

    public function paginate(
        int $perPage,
        int $page,
        ?string $search = null,
        ?bool $featured = null,
        ?bool $heroSection = null
    ): LengthAwarePaginator
    {
        $perPage = min($perPage, 50);
        $search = is_string($search) ? trim($search) : '';
        $selectColumns = $this->projectListSelectColumns();
        $canFilterByFeatured = $featured !== null && $this->hasProjectColumn('is_featured_home');
        $canFilterByHeroSection = $heroSection !== null && $this->hasProjectColumn('project_hero_section');
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

        if ($search !== '' || $canFilterByFeatured || $canFilterByHeroSection) {
            $query = Project::query()
                ->select($selectColumns)
                ->with($this->projectPublicRelations());

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

            if ($canFilterByFeatured) {
                $query->where('is_featured_home', $featured);
            }

            if ($canFilterByHeroSection) {
                $query->where('project_hero_section', $heroSection);
            }

            return $query
                ->orderByDesc('created_at')
                ->paginate($perPage, ['*'], 'page', $page);
        }

        $version = CacheVersion::get('projects');
        $featuredKey = $featured === null ? 'all' : ($featured ? 'featured1' : 'featured0');
        $heroSectionKey = $heroSection === null ? 'heroall' : ($heroSection ? 'hero1' : 'hero0');
        $cacheKey = "public:projects:v{$version}:p{$page}:pp{$perPage}:{$featuredKey}:{$heroSectionKey}";

        return Cache::remember($cacheKey, 3600, function () use ($perPage, $page, $selectColumns, $canFilterByFeatured, $featured, $canFilterByHeroSection, $heroSection) {
            $query = Project::query()
                ->select($selectColumns)
                ->with($this->projectPublicRelations())
                ->orderByDesc('created_at');

            if ($canFilterByFeatured) {
                $query->where('is_featured_home', $featured);
            }

            if ($canFilterByHeroSection) {
                $query->where('project_hero_section', $heroSection);
            }

            return $query->paginate($perPage, ['*'], 'page', $page);
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
            'owner_id',
            'owner_name',
            'owner_name_ar',
            'owner_name_en',
            'owner_title',
            'owner_title_ar',
            'owner_title_en',
            'owner_avatar_url',
            'is_featured_home',
            'project_hero_section',
            'price',
            'status',
            'valuation',
            'yield',
            'property_type',
            'year_built',
            'area_sqft',
            'min_investment',
            'down_payment_percentage',
            'years_of_installment',
            'target_fund',
            'funded_amount',
            'cap_rate',
            'cash_on_cash',
            'irr',
            'features',
        ]);

        return Project::query()
            ->select($selectColumns)
            ->with($this->projectPublicRelations())
            ->findOrFail($id);
    }

    public function create(array $data, ?UploadedFile $cover, ?UploadedFile $ownerAvatar, MediaService $media): Project
    {
        $data = $this->hydrateLegacyFields($data);

        if ($cover) {
            $data['cover_path'] = $media->store($cover, 'projects/covers');
        }

        if ($ownerAvatar) {
            $data['owner_avatar_url'] = $media->store($ownerAvatar, 'projects/owner-avatars');
        }

        $data = $this->filterDataByProjectColumns($data);
        $project = DB::transaction(function () use ($data) {
            $project = Project::create($data);

            if (($data['project_hero_section'] ?? false) === true) {
                $this->clearHeroSectionFromOtherProjects($project->id);
            }

            return $project;
        });
        CacheVersion::bump('projects');

        return $project;
    }

    public function update(Project $project, array $data, ?UploadedFile $cover, ?UploadedFile $ownerAvatar, MediaService $media): Project
    {
        $data = $this->hydrateLegacyFields($data);

        if ($cover) {
            $data['cover_path'] = $media->update($cover, 'projects/covers', $project->cover_path);
        }

        if ($ownerAvatar) {
            $oldOwnerAvatar = $this->isStoredMediaPath($project->owner_avatar_url) ? $project->owner_avatar_url : null;
            $data['owner_avatar_url'] = $media->update($ownerAvatar, 'projects/owner-avatars', $oldOwnerAvatar);
        }

        $data = $this->filterDataByProjectColumns($data);
        $project = DB::transaction(function () use ($project, $data) {
            $project->update($data);

            if (($data['project_hero_section'] ?? false) === true) {
                $this->clearHeroSectionFromOtherProjects($project->id);
            }

            return $project;
        });
        CacheVersion::bump('projects');

        return $project;
    }

    public function delete(Project $project, MediaService $media): void
    {
        $media->delete($project->cover_path);
        if ($this->isStoredMediaPath($project->owner_avatar_url)) {
            $media->delete($project->owner_avatar_url);
        }
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

    public function featuredForHome(int $limit = 8, ?string $status = null): Collection
    {
        if (!$this->hasProjectColumn('is_featured_home')) {
            return new Collection();
        }

        $limit = max(1, min($limit, 30));
        $version = CacheVersion::get('projects');
        $statusKey = $status ?? 'all';
        $cacheKey = "public:home:featured-projects:v{$version}:l{$limit}:s{$statusKey}";
        $selectColumns = $this->projectListSelectColumns();

        return Cache::remember($cacheKey, 3600, function () use ($limit, $selectColumns, $status) {
            $query = Project::query()
                ->select($selectColumns)
                ->with($this->projectPublicRelations())
                ->where('is_featured_home', true)
                ->orderByDesc('updated_at');

            if ($status !== null && $this->hasProjectColumn('status')) {
                $query->where('status', $status);
            }

            return $query
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

        if (isset($data['owner_name_ar']) || isset($data['owner_name_en'])) {
            $data['owner_name'] = $data['owner_name_en'] ?? $data['owner_name_ar'] ?? $data['owner_name'] ?? '';
        }

        if (array_key_exists('owner_title_ar', $data) || array_key_exists('owner_title_en', $data)) {
            $data['owner_title'] = $data['owner_title_en'] ?? $data['owner_title_ar'] ?? $data['owner_title'] ?? null;
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
            'owner_id',
            'owner_name',
            'owner_name_ar',
            'owner_name_en',
            'owner_title',
            'owner_title_ar',
            'owner_title_en',
            'owner_avatar_url',
            'is_featured_home',
            'project_hero_section',
            'price',
            'status',
            'valuation',
            'yield',
            'property_type',
            'year_built',
            'area_sqft',
            'min_investment',
            'down_payment_percentage',
            'years_of_installment',
            'target_fund',
            'funded_amount',
            'cap_rate',
            'cash_on_cash',
            'irr',
            'features',
        ]);
    }

    private function projectPublicRelations(): array
    {
        return [
            'images:id,project_id,name,path,sort_order',
        ];
    }

    private function clearHeroSectionFromOtherProjects(int $projectId): void
    {
        if (!$this->hasProjectColumn('project_hero_section')) {
            return;
        }

        Project::query()
            ->where('project_hero_section', true)
            ->whereKeyNot($projectId)
            ->update(['project_hero_section' => false]);
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

    private function isStoredMediaPath(?string $path): bool
    {
        if (!$path) {
            return false;
        }

        return !str_starts_with($path, 'http://') && !str_starts_with($path, 'https://');
    }
}
