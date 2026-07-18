<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(file_get_contents(database_path('sql/db_rajaongkir.sql')));

        Schema::table('tb_ro_cities', function (Blueprint $table) {
            $table->index('province_id');
            $table->index('city_name');
            $table->index('postal_code');
        });

        Schema::table('tb_ro_subdistricts', function (Blueprint $table) {
            $table->index('city_id');
            $table->index('subdistrict_name');
        });

        Schema::table('mshop_customer_address', function (Blueprint $table) {
            $table->unsignedInteger('ro_city_id')->nullable()->after('city')->index();
            $table->unsignedInteger('ro_subdistrict_id')->nullable()->after('ro_city_id')->index();
        });

        Schema::table('mshop_order_address', function (Blueprint $table) {
            $table->unsignedInteger('ro_city_id')->nullable()->after('city')->index();
            $table->unsignedInteger('ro_subdistrict_id')->nullable()->after('ro_city_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('mshop_order_address', function (Blueprint $table) {
            $table->dropColumn(['ro_city_id', 'ro_subdistrict_id']);
        });

        Schema::table('mshop_customer_address', function (Blueprint $table) {
            $table->dropColumn(['ro_city_id', 'ro_subdistrict_id']);
        });

        Schema::dropIfExists('tb_ro_subdistricts');
        Schema::dropIfExists('tb_ro_cities');
        Schema::dropIfExists('tb_ro_provinces');
    }
};
