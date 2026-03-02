<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('home_contents', function (Blueprint $table) {
            $table->string('hero_background_path')->nullable()->after('youtube_url');
            $table->string('hero_post_path')->nullable()->after('hero_background_path');
        });
    }

    public function down(): void
    {
        Schema::table('home_contents', function (Blueprint $table) {
            $table->dropColumn(['hero_background_path', 'hero_post_path']);
        });
    }
};
