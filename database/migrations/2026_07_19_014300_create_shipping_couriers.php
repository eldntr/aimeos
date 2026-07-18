<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_couriers', function (Blueprint $table) {
            $table->string('code', 32)->primary();
            $table->string('name', 120);
            $table->boolean('supports_domestic_cost')->default(true);
            $table->boolean('supports_international_cost')->default(false);
            $table->boolean('supports_awb')->default(false);
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('seller_shipping_couriers', function (Blueprint $table) {
            $table->id();
            $table->string('siteid', 255)->index();
            $table->string('courier_code', 32);
            $table->timestamps();

            $table->unique(['siteid', 'courier_code']);
            $table->foreign('courier_code')->references('code')->on('shipping_couriers')->cascadeOnDelete();
        });

        $now = now();
        DB::table('shipping_couriers')->insert([
            ['code' => 'jne', 'name' => 'JNE', 'supports_domestic_cost' => true, 'supports_international_cost' => true, 'supports_awb' => true, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'sicepat', 'name' => 'SiCepat', 'supports_domestic_cost' => true, 'supports_international_cost' => false, 'supports_awb' => false, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'ide', 'name' => 'IDExpress', 'supports_domestic_cost' => true, 'supports_international_cost' => false, 'supports_awb' => false, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'sap', 'name' => 'SAP Express', 'supports_domestic_cost' => true, 'supports_international_cost' => false, 'supports_awb' => true, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'ninja', 'name' => 'Ninja', 'supports_domestic_cost' => true, 'supports_international_cost' => false, 'supports_awb' => true, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'jnt', 'name' => 'J&T Express', 'supports_domestic_cost' => true, 'supports_international_cost' => false, 'supports_awb' => true, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'tiki', 'name' => 'TIKI', 'supports_domestic_cost' => true, 'supports_international_cost' => true, 'supports_awb' => true, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'wahana', 'name' => 'Wahana Express', 'supports_domestic_cost' => true, 'supports_international_cost' => false, 'supports_awb' => true, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'pos', 'name' => 'POS Indonesia', 'supports_domestic_cost' => true, 'supports_international_cost' => true, 'supports_awb' => true, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'sentral', 'name' => 'Sentral Cargo', 'supports_domestic_cost' => true, 'supports_international_cost' => false, 'supports_awb' => false, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'lion', 'name' => 'Lion Parcel', 'supports_domestic_cost' => true, 'supports_international_cost' => false, 'supports_awb' => true, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'rex', 'name' => 'Royal Express Asia', 'supports_domestic_cost' => true, 'supports_international_cost' => false, 'supports_awb' => false, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_shipping_couriers');
        Schema::dropIfExists('shipping_couriers');
    }
};
