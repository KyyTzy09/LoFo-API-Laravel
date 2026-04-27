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
        Schema::create('announcements', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('user_id');
            $table->string('item_id')->nullable();

            $table->string('title');
            $table->text('description');

            $table->string('location');
            $table->timestamp('lost_at');

            $table->enum('status', ['PENDING', 'CLOSED'])->default('PENDING');

            $table->timestamps();

            $table->foreign('user_id')->references('userId')->on('users')->cascadeOnDelete();
            $table->foreign('item_id')->references('itemId')->on('items')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
