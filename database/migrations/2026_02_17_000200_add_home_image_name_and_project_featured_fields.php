<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('home_images', function (Blueprint $table) {
            $table->string('name')->nullable()->after('home_content_id');
        });

        Schema::table('project_images', function (Blueprint $table) {
            $table->string('name')->nullable()->after('project_id');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->boolean('is_featured_home')->default(false)->after('cover_path');
            $table->index('is_featured_home');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['is_featured_home']);
            $table->dropColumn('is_featured_home');
        });

        Schema::table('project_images', function (Blueprint $table) {
            $table->dropColumn('name');
        });

        Schema::table('home_images', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
};
