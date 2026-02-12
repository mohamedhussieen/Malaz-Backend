<?php

namespace Database\Seeders;

use App\Enums\ContactMessageStatus;
use App\Enums\PlatformLinkKey;
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

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Cache::flush();

        DB::transaction(function (): void {
            DB::table('personal_access_tokens')->delete();
            ProjectImage::query()->delete();
            HomeImage::query()->delete();
            Project::query()->delete();
            Owner::query()->delete();
            PlatformLink::query()->delete();
            ContactMessage::query()->delete();
            HomeContent::query()->delete();
            User::query()->delete();

            $now = now();

            User::query()->insert([
                'id' => 1,
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => Hash::make('secret'),
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            Owner::query()->insert([
                [
                    'id' => 1,
                    'name' => 'Malaz Owner',
                    'name_ar' => 'مالك ملاز',
                    'name_en' => 'Malaz Owner',
                    'title' => 'Chief Executive Officer',
                    'title_ar' => 'الرئيس التنفيذي',
                    'title_en' => 'Chief Executive Officer',
                    'bio' => 'Leads strategy, partnerships, and delivery across all projects.',
                    'bio_ar' => 'يقود الاستراتيجية والشراكات وتنفيذ المشاريع.',
                    'bio_en' => 'Leads strategy, partnerships, and delivery across all projects.',
                    'avatar_path' => 'owners/avatars/seed-owner-1.jpg',
                    'created_at' => $now->copy()->subDays(12),
                    'updated_at' => $now->copy()->subDays(2),
                ],
                [
                    'id' => 2,
                    'name' => 'Operations Lead',
                    'name_ar' => 'مدير العمليات',
                    'name_en' => 'Operations Lead',
                    'title' => 'Operations Director',
                    'title_ar' => 'مدير التشغيل',
                    'title_en' => 'Operations Director',
                    'bio' => 'Oversees implementation standards and quality controls.',
                    'bio_ar' => 'يشرف على معايير التنفيذ وضبط الجودة.',
                    'bio_en' => 'Oversees implementation standards and quality controls.',
                    'avatar_path' => 'owners/avatars/seed-owner-2.jpg',
                    'created_at' => $now->copy()->subDays(10),
                    'updated_at' => $now->copy()->subDay(),
                ],
            ]);

            Project::query()->insert([
                [
                    'id' => 1,
                    'name' => 'Malaz Villas',
                    'name_ar' => 'فلل ملاز',
                    'name_en' => 'Malaz Villas',
                    'description' => 'Premium residential villa development with integrated landscape design and smart home systems.',
                    'description_ar' => 'مشروع فلل سكنية فاخرة مع تصميم متكامل وأنظمة منزل ذكي.',
                    'description_en' => 'Premium residential villa development with integrated landscape design and smart home systems.',
                    'location' => 'Riyadh',
                    'location_ar' => 'الرياض',
                    'location_en' => 'Riyadh',
                    'cover_path' => 'projects/covers/seed-project-1.jpg',
                    'created_at' => $now->copy()->subDays(14),
                    'updated_at' => $now->copy()->subDays(3),
                ],
                [
                    'id' => 2,
                    'name' => 'Malaz Business Hub',
                    'name_ar' => 'مركز ملاز للأعمال',
                    'name_en' => 'Malaz Business Hub',
                    'description' => 'Commercial office complex focused on modular workspace and efficient infrastructure.',
                    'description_ar' => 'مجمع مكاتب تجاري يركز على المرونة وكفاءة البنية التحتية.',
                    'description_en' => 'Commercial office complex focused on modular workspace and efficient infrastructure.',
                    'location' => 'Jeddah',
                    'location_ar' => 'جدة',
                    'location_en' => 'Jeddah',
                    'cover_path' => 'projects/covers/seed-project-2.jpg',
                    'created_at' => $now->copy()->subDays(9),
                    'updated_at' => $now->copy()->subDays(2),
                ],
            ]);

            ProjectImage::query()->insert([
                [
                    'id' => 1,
                    'project_id' => 1,
                    'path' => 'projects/galleries/seed-project-1-1.jpg',
                    'sort_order' => 0,
                    'created_at' => $now->copy()->subDays(8),
                    'updated_at' => $now->copy()->subDays(8),
                ],
                [
                    'id' => 2,
                    'project_id' => 1,
                    'path' => 'projects/galleries/seed-project-1-2.jpg',
                    'sort_order' => 1,
                    'created_at' => $now->copy()->subDays(7),
                    'updated_at' => $now->copy()->subDays(7),
                ],
                [
                    'id' => 3,
                    'project_id' => 2,
                    'path' => 'projects/galleries/seed-project-2-1.jpg',
                    'sort_order' => 0,
                    'created_at' => $now->copy()->subDays(6),
                    'updated_at' => $now->copy()->subDays(6),
                ],
            ]);

            HomeContent::query()->insert([
                'id' => 1,
                'headline_text' => 'Building spaces with purpose',
                'headline_text_ar' => 'نبني مساحات لها معنى',
                'headline_text_en' => 'Building spaces with purpose',
                'body_text' => 'Malaz delivers residential and commercial projects with a balance of design, performance, and long-term value.',
                'body_text_ar' => 'تقدم ملاز مشاريع سكنية وتجارية تجمع بين التصميم والأداء والقيمة طويلة الأمد.',
                'body_text_en' => 'Malaz delivers residential and commercial projects with a balance of design, performance, and long-term value.',
                'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'created_at' => $now->copy()->subDays(15),
                'updated_at' => $now->copy()->subDays(1),
            ]);

            HomeImage::query()->insert([
                [
                    'id' => 1,
                    'home_content_id' => 1,
                    'path' => 'home/hero_gallery/seed-hero-1.jpg',
                    'sort_order' => 0,
                    'created_at' => $now->copy()->subDays(5),
                    'updated_at' => $now->copy()->subDays(5),
                ],
                [
                    'id' => 2,
                    'home_content_id' => 1,
                    'path' => 'home/hero_gallery/seed-hero-2.jpg',
                    'sort_order' => 1,
                    'created_at' => $now->copy()->subDays(4),
                    'updated_at' => $now->copy()->subDays(4),
                ],
            ]);

            PlatformLink::query()->insert([
                [
                    'id' => 1,
                    'key' => PlatformLinkKey::Facebook->value,
                    'url' => 'https://facebook.com/malaz',
                    'is_active' => true,
                    'created_at' => $now->copy()->subDays(3),
                    'updated_at' => $now->copy()->subDays(3),
                ],
                [
                    'id' => 2,
                    'key' => PlatformLinkKey::Instagram->value,
                    'url' => 'https://instagram.com/malaz',
                    'is_active' => true,
                    'created_at' => $now->copy()->subDays(3),
                    'updated_at' => $now->copy()->subDays(3),
                ],
                [
                    'id' => 3,
                    'key' => PlatformLinkKey::Linkedin->value,
                    'url' => 'https://linkedin.com/company/malaz',
                    'is_active' => true,
                    'created_at' => $now->copy()->subDays(3),
                    'updated_at' => $now->copy()->subDays(3),
                ],
                [
                    'id' => 4,
                    'key' => PlatformLinkKey::Whatsapp->value,
                    'url' => 'https://wa.me/966500000000',
                    'is_active' => false,
                    'created_at' => $now->copy()->subDays(3),
                    'updated_at' => $now->copy()->subDays(3),
                ],
            ]);

            ContactMessage::query()->insert([
                [
                    'id' => 1,
                    'name' => 'Client One',
                    'email' => 'client.one@example.com',
                    'phone' => '01000000001',
                    'whatsapp' => '01000000001',
                    'note' => 'Need project pricing details.',
                    'status' => ContactMessageStatus::New->value,
                    'created_at' => $now->copy()->subDays(2),
                    'updated_at' => $now->copy()->subDays(2),
                ],
                [
                    'id' => 2,
                    'name' => 'Client Two',
                    'email' => 'client.two@example.com',
                    'phone' => '01000000002',
                    'whatsapp' => '01000000002',
                    'note' => 'Asking about construction timeline.',
                    'status' => ContactMessageStatus::Read->value,
                    'created_at' => $now->copy()->subDay(),
                    'updated_at' => $now->copy()->subDay(),
                ],
                [
                    'id' => 3,
                    'name' => 'Client Three',
                    'email' => 'client.three@example.com',
                    'phone' => null,
                    'whatsapp' => null,
                    'note' => 'Interested in partnership opportunities.',
                    'status' => ContactMessageStatus::Archived->value,
                    'created_at' => $now->copy()->subHours(12),
                    'updated_at' => $now->copy()->subHours(12),
                ],
            ]);
        });
    }
}
