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
        Schema::create('nutrition_logs', function (Blueprint $table) {
            $table->id();

            //foreign key
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            //informasi nutrisi
            $table->string('food_name');
            $table->integer('calories');
            $table->decimal('protein_g');
            $table->decimal('carbs_g');
            $table->decimal('fat_g');
            $table->enum('meal_type', ['sarapan', 'makan_siang', 'makan_malam', 'camilan']);

            $table->timestamp('consumed_at')->useCurrent();
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
