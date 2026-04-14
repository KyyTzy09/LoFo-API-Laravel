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
            $table->string('itemId')->primary(); // ULID manual
            $table->string('image');
            $table->string('item_name');
            $table->text('item_info')->nullable();
            $table->enum('status', ['TERSEDIA', 'HILANG'])->default('TERSEDIA');
            $table->text('qr_url')->nullable();

            $table->timestamps();

            // foreign key
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
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
