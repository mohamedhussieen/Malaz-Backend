<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('owners', function (Blueprint $table) {
            $table->string('name_ar')->nullable()->after('name');
            $table->string('name_en')->nullable()->after('name_ar');
            $table->string('title_ar')->nullable()->after('title');
            $table->string('title_en')->nullable()->after('title_ar');
            $table->text('bio_ar')->nullable()->after('bio');
            $table->text('bio_en')->nullable()->after('bio_ar');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->string('name_ar')->nullable()->after('name');
            $table->string('name_en')->nullable()->after('name_ar');
            $table->text('description_ar')->nullable()->after('description');
            $table->text('description_en')->nullable()->after('description_ar');
            $table->string('location_ar')->nullable()->after('location');
            $table->string('location_en')->nullable()->after('location_ar');
        });

        Schema::table('home_contents', function (Blueprint $table) {
            $table->string('headline_text_ar')->nullable()->after('headline_text');
            $table->string('headline_text_en')->nullable()->after('headline_text_ar');
            $table->text('body_text_ar')->nullable()->after('body_text');
            $table->text('body_text_en')->nullable()->after('body_text_ar');
        });

        DB::table('owners')->update([
            'name_ar' => DB::raw('COALESCE(name_ar, name)'),
            'name_en' => DB::raw('COALESCE(name_en, name)'),
            'title_ar' => DB::raw('COALESCE(title_ar, title)'),
            'title_en' => DB::raw('COALESCE(title_en, title)'),
            'bio_ar' => DB::raw('COALESCE(bio_ar, bio)'),
            'bio_en' => DB::raw('COALESCE(bio_en, bio)'),
        ]);

        DB::table('projects')->update([
            'name_ar' => DB::raw('COALESCE(name_ar, name)'),
            'name_en' => DB::raw('COALESCE(name_en, name)'),
            'description_ar' => DB::raw('COALESCE(description_ar, description)'),
            'description_en' => DB::raw('COALESCE(description_en, description)'),
            'location_ar' => DB::raw('COALESCE(location_ar, location)'),
            'location_en' => DB::raw('COALESCE(location_en, location)'),
        ]);

        DB::table('home_contents')->update([
            'headline_text_ar' => DB::raw('COALESCE(headline_text_ar, headline_text)'),
            'headline_text_en' => DB::raw('COALESCE(headline_text_en, headline_text)'),
            'body_text_ar' => DB::raw('COALESCE(body_text_ar, body_text)'),
            'body_text_en' => DB::raw('COALESCE(body_text_en, body_text)'),
        ]);
    }

    public function down(): void
    {
        Schema::table('owners', function (Blueprint $table) {
            $table->dropColumn(['name_ar', 'name_en', 'title_ar', 'title_en', 'bio_ar', 'bio_en']);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'name_ar',
                'name_en',
                'description_ar',
                'description_en',
                'location_ar',
                'location_en',
            ]);
        });

        Schema::table('home_contents', function (Blueprint $table) {
            $table->dropColumn(['headline_text_ar', 'headline_text_en', 'body_text_ar', 'body_text_en']);
        });
    }
};
