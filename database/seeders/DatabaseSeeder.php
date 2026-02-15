<?php

namespace Database\Seeders;

use App\Enums\ContactMessageStatus;
use App\Enums\PlatformLinkKey;
use App\Models\Blog;
use App\Models\ContactMessage;
use App\Models\HomeContent;
use App\Models\HomeImage;
use App\Models\Owner;
use App\Models\PlatformLink;
use App\Models\Project;
use App\Models\ProjectImage;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    private const ROWS_PER_TABLE = 10000;
    private const CHUNK_SIZE = 500;

    public function run(): void
    {
        Cache::flush();
        $this->resetTables();

        $now = now();
        $faker = fake();

        DB::transaction(function () use ($now, $faker): void {
            $this->seedUsers($now);
            $this->seedOwners($now, $faker);
            $this->seedProjects($now, $faker);
            $this->seedProjectImages($now);
            $this->seedHomeContents($now, $faker);
            $this->seedHomeImages($now);
            $this->seedPlatformLinks($now);
            $this->seedContactMessages($now, $faker);
            $this->seedBlogs($now, $faker);
        });
    }

    private function resetTables(): void
    {
        $tables = [
            'personal_access_tokens',
            'project_images',
            'home_images',
            'blogs',
            'projects',
            'owners',
            'platform_links',
            'contact_messages',
            'home_contents',
            'users',
        ];

        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
            foreach ($tables as $table) {
                DB::table($table)->delete();
            }
            DB::statement("DELETE FROM sqlite_sequence WHERE name IN ('users','owners','projects','project_images','home_contents','home_images','platform_links','contact_messages','blogs','personal_access_tokens');");
            DB::statement('PRAGMA foreign_keys = ON;');

            return;
        }

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            foreach ($tables as $table) {
                DB::table($table)->truncate();
            }
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('TRUNCATE TABLE ' . implode(', ', $tables) . ' RESTART IDENTITY CASCADE;');

            return;
        }

        foreach ($tables as $table) {
            DB::table($table)->delete();
        }
    }

    private function seedUsers($now): void
    {
        $hashedPassword = Hash::make('secret');

        $users = [[
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => $hashedPassword,
            'email_verified_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]];

        for ($i = 2; $i <= self::ROWS_PER_TABLE; $i++) {
            $users[] = [
                'name' => "Admin User {$i}",
                'email' => "admin{$i}@example.com",
                'password' => $hashedPassword,
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($users, self::CHUNK_SIZE) as $chunk) {
            User::query()->insert($chunk);
        }
    }

    private function seedOwners($now, $faker): void
    {
        $this->bulkInsert(Owner::class, function (int $i) use ($now, $faker): array {
            $nameEn = "Owner {$i}";
            $nameAr = "مالك {$i}";
            $titleEn = $faker->jobTitle();
            $titleAr = "منصب {$i}";
            $bioEn = $faker->paragraph();
            $bioAr = "نبذة تعريفية {$i}";

            return [
                'name' => $nameEn,
                'name_ar' => $nameAr,
                'name_en' => $nameEn,
                'title' => $titleEn,
                'title_ar' => $titleAr,
                'title_en' => $titleEn,
                'bio' => $bioEn,
                'bio_ar' => $bioAr,
                'bio_en' => $bioEn,
                'avatar_path' => "owners/avatars/seed-owner-{$i}.jpg",
                'created_at' => $now,
                'updated_at' => $now,
            ];
        });
    }

    private function seedProjects($now, $faker): void
    {
        $this->bulkInsert(Project::class, function (int $i) use ($now, $faker): array {
            $nameEn = "Project {$i}";
            $nameAr = "مشروع {$i}";
            $descriptionEn = $faker->paragraph();
            $descriptionAr = "وصف المشروع {$i}";
            $locationEn = $faker->city();
            $locationAr = "مدينة {$i}";

            return [
                'name' => $nameEn,
                'name_ar' => $nameAr,
                'name_en' => $nameEn,
                'description' => $descriptionEn,
                'description_ar' => $descriptionAr,
                'description_en' => $descriptionEn,
                'location' => $locationEn,
                'location_ar' => $locationAr,
                'location_en' => $locationEn,
                'cover_path' => "projects/covers/seed-project-{$i}.jpg",
                'created_at' => $now,
                'updated_at' => $now,
            ];
        });
    }

    private function seedProjectImages($now): void
    {
        $projectIds = Project::query()->pluck('id')->all();

        $rows = [];
        $count = count($projectIds);
        for ($i = 1; $i <= self::ROWS_PER_TABLE; $i++) {
            $projectId = $projectIds[($i - 1) % $count];
            $rows[] = [
                'project_id' => $projectId,
                'path' => "projects/galleries/seed-project-image-{$i}.jpg",
                'sort_order' => (($i - 1) % 5),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($rows) >= self::CHUNK_SIZE) {
                ProjectImage::query()->insert($rows);
                $rows = [];
            }
        }

        if ($rows !== []) {
            ProjectImage::query()->insert($rows);
        }
    }

    private function seedHomeContents($now, $faker): void
    {
        $this->bulkInsert(HomeContent::class, function (int $i) use ($now, $faker): array {
            $headlineEn = "Headline {$i}";
            $headlineAr = "عنوان {$i}";
            $bodyEn = $faker->sentence(16);
            $bodyAr = "وصف مختصر {$i}";

            return [
                'headline_text' => $headlineEn,
                'headline_text_ar' => $headlineAr,
                'headline_text_en' => $headlineEn,
                'body_text' => $bodyEn,
                'body_text_ar' => $bodyAr,
                'body_text_en' => $bodyEn,
                'youtube_url' => "https://www.youtube.com/watch?v={$i}",
                'created_at' => $now,
                'updated_at' => $now,
            ];
        });
    }

    private function seedHomeImages($now): void
    {
        $homeContentIds = HomeContent::query()->pluck('id')->all();

        $rows = [];
        $count = count($homeContentIds);
        for ($i = 1; $i <= self::ROWS_PER_TABLE; $i++) {
            $homeContentId = $homeContentIds[($i - 1) % $count];
            $rows[] = [
                'home_content_id' => $homeContentId,
                'path' => "home/hero_gallery/seed-hero-{$i}.jpg",
                'sort_order' => (($i - 1) % 8),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($rows) >= self::CHUNK_SIZE) {
                HomeImage::query()->insert($rows);
                $rows = [];
            }
        }

        if ($rows !== []) {
            HomeImage::query()->insert($rows);
        }
    }

    private function seedPlatformLinks($now): void
    {
        $keys = array_column(PlatformLinkKey::cases(), 'value');

        $rows = [];
        $keyCount = count($keys);
        for ($i = 1; $i <= self::ROWS_PER_TABLE; $i++) {
            $key = $keys[($i - 1) % $keyCount];
            $rows[] = [
                'key' => $key,
                'url' => "https://example.com/{$key}/{$i}",
                'is_active' => $i % 2 === 0,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($rows) >= self::CHUNK_SIZE) {
                PlatformLink::query()->insert($rows);
                $rows = [];
            }
        }

        if ($rows !== []) {
            PlatformLink::query()->insert($rows);
        }
    }

    private function seedContactMessages($now, $faker): void
    {
        $statuses = array_column(ContactMessageStatus::cases(), 'value');

        $rows = [];
        $statusCount = count($statuses);
        for ($i = 1; $i <= self::ROWS_PER_TABLE; $i++) {
            $rows[] = [
                'name' => "Client {$i}",
                'email' => "client{$i}@example.com",
                'phone' => sprintf('010%08d', $i),
                'whatsapp' => sprintf('010%08d', $i),
                'note' => $faker->sentence(12),
                'status' => $statuses[($i - 1) % $statusCount],
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($rows) >= self::CHUNK_SIZE) {
                ContactMessage::query()->insert($rows);
                $rows = [];
            }
        }

        if ($rows !== []) {
            ContactMessage::query()->insert($rows);
        }
    }

    private function seedBlogs($now, $faker): void
    {
        $this->bulkInsert(Blog::class, function (int $i) use ($now, $faker): array {
            $titleEn = "Blog Post {$i}";
            $titleAr = "مقال {$i}";
            $excerptEn = $faker->sentence(18);
            $excerptAr = "ملخص المقال {$i}";
            $contentEn = $faker->paragraphs(4, true);
            $contentAr = "محتوى المقال {$i}";
            $publishedAt = $i % 3 === 0 ? null : $now->copy()->subDays($i % 365);

            return [
                'title' => $titleEn,
                'title_ar' => $titleAr,
                'title_en' => $titleEn,
                'excerpt' => $excerptEn,
                'excerpt_ar' => $excerptAr,
                'excerpt_en' => $excerptEn,
                'content' => $contentEn,
                'content_ar' => $contentAr,
                'content_en' => $contentEn,
                'slug' => Str::slug($titleEn) . "-{$i}",
                'cover_path' => "blogs/covers/seed-blog-{$i}.jpg",
                'is_published' => $i % 5 !== 0,
                'published_at' => $publishedAt,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        });
    }

    private function bulkInsert(string $modelClass, callable $rowGenerator): void
    {
        $rows = [];
        for ($i = 1; $i <= self::ROWS_PER_TABLE; $i++) {
            $rows[] = $rowGenerator($i);

            if (count($rows) >= self::CHUNK_SIZE) {
                $modelClass::query()->insert($rows);
                $rows = [];
            }
        }

        if ($rows !== []) {
            $modelClass::query()->insert($rows);
        }
    }
}
