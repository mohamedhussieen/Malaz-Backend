<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_paragraphs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_id')->constrained()->cascadeOnDelete();
            $table->string('header')->nullable();
            $table->string('header_ar')->nullable();
            $table->string('header_en')->nullable();
            $table->longText('content');
            $table->longText('content_ar')->nullable();
            $table->longText('content_en')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['blog_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_paragraphs');
    }
};
