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
            $table->string('video')->nullable();
        });

        // Ensure all locale currencies are IDR to avoid "Price item not available" on VPS/Prod
        if (Schema::hasTable('mshop_locale')) {
            \Illuminate\Support\Facades\DB::table('mshop_locale')->update(['currencyid' => 'IDR']);
        }

        // Ensure IDR currency is active to avoid "Locale item for site ... not found" during bootstrap
        if (Schema::hasTable('mshop_locale_currency')) {
            \Illuminate\Support\Facades\DB::table('mshop_locale_currency')
                ->where('id', 'IDR')
                ->update(['status' => 1]);
        }

        // Ensure Indonesian language is active
        if (Schema::hasTable('mshop_locale_language')) {
            \Illuminate\Support\Facades\DB::table('mshop_locale_language')
                ->where('id', 'id')
                ->update(['status' => 1]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mshop_product', function (Blueprint $table) {
            $table->dropColumn('video');
        });
    }
};
