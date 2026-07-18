<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('komerce_destinations', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('province_id')->nullable()->index();
            $table->string('province_name', 100);
            $table->unsignedBigInteger('city_id')->nullable()->index();
            $table->string('city_name', 100);
            $table->unsignedBigInteger('district_id')->nullable()->index();
            $table->string('district_name', 100);
            $table->string('subdistrict_name', 100);
            $table->string('zip_code', 10)->nullable()->index();
            $table->string('label', 500);
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->index(['province_name', 'city_name', 'district_name']);
            $table->index('subdistrict_name');
        });

        Schema::table('tb_ro_subdistricts', function (Blueprint $table) {
            $table->unsignedBigInteger('komerce_destination_id')->nullable()->after('subdistrict_name')->index();
        });

        Schema::table('mshop_customer_address', function (Blueprint $table) {
            $table->unsignedBigInteger('komerce_destination_id')->nullable()->after('ro_subdistrict_id')->index();
        });

        Schema::table('mshop_order_address', function (Blueprint $table) {
            $table->unsignedBigInteger('komerce_destination_id')->nullable()->after('ro_subdistrict_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('mshop_order_address', function (Blueprint $table) {
            $table->dropColumn('komerce_destination_id');
        });

        Schema::table('mshop_customer_address', function (Blueprint $table) {
            $table->dropColumn('komerce_destination_id');
        });

        Schema::table('tb_ro_subdistricts', function (Blueprint $table) {
            $table->dropColumn('komerce_destination_id');
        });

        Schema::dropIfExists('komerce_destinations');
    }
};
