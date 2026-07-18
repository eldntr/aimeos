<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_blocked_keywords', function (Blueprint $table) {
            $table->id();
            $table->string('keyword');
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['is_active']);
        });

        // Seed some default blocked keywords
        DB::table('chat_blocked_keywords')->insert([
            ['keyword' => 'cod aja', 'is_active' => true, 'created_by' => null, 'created_at' => now(), 'updated_at' => now()],
            ['keyword' => 'shopee aja', 'is_active' => true, 'created_by' => null, 'created_at' => now(), 'updated_at' => now()],
            ['keyword' => 'tokopedia aja', 'is_active' => true, 'created_by' => null, 'created_at' => now(), 'updated_at' => now()],
            ['keyword' => 'ketemu di toko', 'is_active' => true, 'created_by' => null, 'created_at' => now(), 'updated_at' => now()],
            ['keyword' => 'wa saya', 'is_active' => true, 'created_by' => null, 'created_at' => now(), 'updated_at' => now()],
            ['keyword' => 'transfer dulu', 'is_active' => true, 'created_by' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_blocked_keywords');
    }
};
