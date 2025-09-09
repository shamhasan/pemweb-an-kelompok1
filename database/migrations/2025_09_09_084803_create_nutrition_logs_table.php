<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // database/migrations/xxxx_xx_xx_xxxxxx_create_nutrition_logs_table.php
    public function up(): void
    {
        Schema::create('nutrition_logs', function (Blueprint $table) {
            $table->id(); // Sesuai ERD
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Sesuai ERD
            $table->string('food_name'); // Sesuai ERD
            $table->integer('calories'); // Sesuai ERD
            $table->decimal('protein_g', 8, 2); // Sesuai ERD
            $table->decimal('carbs_g', 8, 2); // Sesuai ERD
            $table->decimal('fat_g', 8, 2); // Sesuai ERD
            $table->enum('meal_type', ['sarapan', 'makan_siang', 'makan_malam', 'camilan']); // Sesuai ERD
            $table->timestamp('consumed_at'); // Sesuai ERD
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nutrition_logs');
    }
};
