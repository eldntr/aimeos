<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mshop_product', function (Blueprint $table) {
            if (!Schema::hasColumn('mshop_product', 'weight_grams')) {
                $table->unsignedInteger('weight_grams')->default(1000)->after('instock');
            }
        });
    }

    public function down(): void
    {
        Schema::table('mshop_product', function (Blueprint $table) {
            if (Schema::hasColumn('mshop_product', 'weight_grams')) {
                $table->dropColumn('weight_grams');
            }
        });
    }
};
