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
        Schema::create('recommendations', function (Blueprint $table) {
            $table->id();

            // foreign key ke user
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // info terkait recomendations
            $table->string('title');
            $table->text('description');
            $table->enum('type', ['artikel', 'aktivitas', 'nutrisi']);
            $table->string('related_url')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recomendations');
    }
};
