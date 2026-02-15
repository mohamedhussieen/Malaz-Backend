<?php

namespace App\Services;

use App\Models\Blog;
use App\Support\CacheVersion;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class BlogService
{
    public function paginateAdmin(int $perPage, int $page): LengthAwarePaginator
    {
        $perPage = min($perPage, 50);

        return Blog::query()
            ->select($this->selectColumns())
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function paginatePublic(int $perPage, int $page): LengthAwarePaginator
    {
        $perPage = min($perPage, 50);
        $version = CacheVersion::get('blogs');
        $cacheKey = "public:blogs:v{$version}:p{$page}:pp{$perPage}";

        return Cache::remember($cacheKey, 3600, function () use ($perPage, $page) {
            return Blog::query()
                ->select($this->selectColumns())
                ->where('is_published', true)
                ->where(function ($query) {
                    $query
                        ->whereNull('published_at')
                        ->orWhere('published_at', '<=', now());
                })
                ->orderByDesc('published_at')
                ->orderByDesc('id')
                ->paginate($perPage, ['*'], 'page', $page);
        });
    }

    public function findPublicBySlug(string $slug): Blog
    {
        $version = CacheVersion::get('blogs');
        $cacheKey = "public:blogs:v{$version}:slug:{$slug}";

        return Cache::remember($cacheKey, 3600, function () use ($slug) {
            $blog = Blog::query()
                ->select($this->selectColumns())
                ->where('slug', $slug)
                ->where('is_published', true)
                ->where(function ($query) {
                    $query
                        ->whereNull('published_at')
                        ->orWhere('published_at', '<=', now());
                })
                ->first();

            if (!$blog) {
                throw (new ModelNotFoundException())->setModel(Blog::class, [$slug]);
            }

            return $blog;
        });
    }

    public function create(array $data, ?UploadedFile $cover, MediaService $media): Blog
    {
        $data = $this->hydrateLegacyFields($data);
        $data = $this->preparePublishing($data);
        $data['slug'] = $this->prepareSlug($data['slug'] ?? null, $data['title_en'] ?? $data['title'] ?? 'blog-post');

        if ($cover) {
            $data['cover_path'] = $media->store($cover, 'blogs/covers');
        }

        $blog = Blog::create($data);
        CacheVersion::bump('blogs');

        return $blog;
    }

    public function update(Blog $blog, array $data, ?UploadedFile $cover, MediaService $media): Blog
    {
        $data = $this->hydrateLegacyFields($data);
        $data = $this->preparePublishing($data);

        if (array_key_exists('slug', $data)) {
            $data['slug'] = $this->prepareSlug(
                $data['slug'],
                $data['title_en'] ?? $data['title'] ?? $blog->title_en ?? $blog->title ?? 'blog-post',
                $blog->id
            );
        }

        if ($cover) {
            $data['cover_path'] = $media->update($cover, 'blogs/covers', $blog->cover_path);
        }

        $blog->update($data);
        CacheVersion::bump('blogs');

        return $blog;
    }

    public function delete(Blog $blog, MediaService $media): void
    {
        $media->delete($blog->cover_path);
        $blog->delete();
        CacheVersion::bump('blogs');
    }

    private function selectColumns(): array
    {
        return [
            'id',
            'title',
            'title_ar',
            'title_en',
            'excerpt',
            'excerpt_ar',
            'excerpt_en',
            'content',
            'content_ar',
            'content_en',
            'slug',
            'cover_path',
            'is_published',
            'published_at',
            'created_at',
            'updated_at',
        ];
    }

    private function hydrateLegacyFields(array $data): array
    {
        if (isset($data['title_ar']) || isset($data['title_en'])) {
            $data['title'] = $data['title_en'] ?? $data['title_ar'] ?? $data['title'] ?? '';
        }

        if (array_key_exists('excerpt_ar', $data) || array_key_exists('excerpt_en', $data)) {
            $data['excerpt'] = $data['excerpt_en'] ?? $data['excerpt_ar'] ?? null;
        }

        if (isset($data['content_ar']) || isset($data['content_en'])) {
            $data['content'] = $data['content_en'] ?? $data['content_ar'] ?? $data['content'] ?? '';
        }

        return $data;
    }

    private function preparePublishing(array $data): array
    {
        if (($data['is_published'] ?? null) === true && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        if (($data['is_published'] ?? null) === false && !array_key_exists('published_at', $data)) {
            $data['published_at'] = null;
        }

        return $data;
    }

    private function prepareSlug(?string $incomingSlug, string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($incomingSlug ?: $title);
        if ($baseSlug === '') {
            $baseSlug = 'blog-post';
        }

        $slug = $baseSlug;
        $counter = 2;

        while ($this->slugExists($slug, $ignoreId)) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    private function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        $query = Blog::query()->where('slug', $slug);

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }
}
