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
        Schema::create('device_tokens', function (Blueprint $table) {
            // Jika tabel device_tokens ini juga mau pakai UUID sebagai Primary Key-nya:
            $table->uuid('id')->primary();
            $table->string('user_id');
            $table->string('token')->unique();

            $table->foreign('user_id')
                ->references('userId')
                ->on('users')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_tokens');
    }
};
