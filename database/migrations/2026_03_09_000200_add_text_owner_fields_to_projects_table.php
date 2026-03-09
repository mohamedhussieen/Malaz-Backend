<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('owner_name')->nullable()->after('owner_id');
            $table->string('owner_name_ar')->nullable()->after('owner_name');
            $table->string('owner_name_en')->nullable()->after('owner_name_ar');
            $table->string('owner_title')->nullable()->after('owner_name_en');
            $table->string('owner_title_ar')->nullable()->after('owner_title');
            $table->string('owner_title_en')->nullable()->after('owner_title_ar');
            $table->string('owner_avatar_url')->nullable()->after('owner_title_en');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'owner_name',
                'owner_name_ar',
                'owner_name_en',
                'owner_title',
                'owner_title_ar',
                'owner_title_en',
                'owner_avatar_url',
            ]);
        });
    }
};
