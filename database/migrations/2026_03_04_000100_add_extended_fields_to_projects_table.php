<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->unsignedBigInteger('price')->nullable()->after('is_featured_home');
            $table->string('status', 50)->default('available')->after('price');
            $table->unsignedBigInteger('valuation')->nullable()->after('status');
            $table->decimal('yield', 6, 2)->nullable()->after('valuation');
            $table->json('property_type')->nullable()->after('yield');
            $table->unsignedSmallInteger('year_built')->nullable()->after('property_type');
            $table->unsignedInteger('area_sqft')->nullable()->after('year_built');
            $table->json('features')->nullable()->after('area_sqft');

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn([
                'price',
                'status',
                'valuation',
                'yield',
                'property_type',
                'year_built',
                'area_sqft',
                'features',
            ]);
        });
    }
};
