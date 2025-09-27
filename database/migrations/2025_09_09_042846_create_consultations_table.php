<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->enum('status', ['aktif', 'selesai']);
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('ended_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};
