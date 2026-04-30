<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->string('itemId')->primary();
            $table->string('user_id');

            $table->string('image');
            $table->string('item_name');
            $table->text('item_info')->nullable();

            $table->enum('status', ['TERSEDIA', 'HILANG'])->default('TERSEDIA');

            $table->text('qr_url')->nullable();
            
            $table->timestamp('last_seen_at')->nullable();
            
            $table->timestamps();

            $table->foreign('user_id')->references('userId')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
