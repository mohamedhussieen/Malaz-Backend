<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_links', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->string('url');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('key');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_links');
    }
};
