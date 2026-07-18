<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mshop_product', function (Blueprint $table) {
            $table->boolean('free_shipping')->default(false)->index();
            $table->boolean('voucher_eligible')->default(false)->index();
            $table->string('brand_tier', 20)->default('normal')->index();
            $table->boolean('eco_friendly')->default(false)->index();
            $table->boolean('is_clearance')->default(false)->index();
            $table->boolean('supports_cod')->default(false)->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mshop_product', function (Blueprint $table) {
            $table->dropColumn([
                'free_shipping',
                'voucher_eligible',
                'brand_tier',
                'eco_friendly',
                'is_clearance',
                'supports_cod'
            ]);
        });
    }
};
