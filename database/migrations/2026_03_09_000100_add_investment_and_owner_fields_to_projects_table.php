<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('owner_id')
                ->nullable()
                ->after('cover_path')
                ->constrained('owners')
                ->nullOnDelete();

            $table->unsignedBigInteger('min_investment')->nullable()->after('area_sqft');
            $table->unsignedBigInteger('target_fund')->nullable()->after('min_investment');
            $table->unsignedBigInteger('funded_amount')->nullable()->after('target_fund');
            $table->decimal('cap_rate', 6, 2)->nullable()->after('funded_amount');
            $table->decimal('cash_on_cash', 6, 2)->nullable()->after('cap_rate');
            $table->decimal('irr', 6, 2)->nullable()->after('cash_on_cash');

            $table->index('owner_id');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['owner_id']);
            $table->dropForeign(['owner_id']);
            $table->dropColumn([
                'owner_id',
                'min_investment',
                'target_fund',
                'funded_amount',
                'cap_rate',
                'cash_on_cash',
                'irr',
            ]);
        });
    }
};
