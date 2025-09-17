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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();

            //foreign key ke consultation
            $table->foreignId('consultation_id')->constrained('consultations')->onDelete('cascade');

            //info messages
            $table->enum('sender_type', ['user', 'ai'])->default('user');
            $table->text('content');
            $table->timestamp('sent_at')->useCurrent();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
