<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->decimal('down_payment_percentage', 5, 2)
                ->nullable()
                ->after('min_investment');
            $table->unsignedSmallInteger('years_of_installment')
                ->nullable()
                ->after('down_payment_percentage');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'down_payment_percentage',
                'years_of_installment',
            ]);
        });
    }
};
