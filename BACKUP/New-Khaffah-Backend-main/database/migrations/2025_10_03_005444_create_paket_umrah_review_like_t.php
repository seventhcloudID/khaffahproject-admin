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
        Schema::create('paket_umrah_review_like_t', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(true);
            $table->foreignId('review_id')->constrained('paket_umrah_review_t')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id'); // relasi ke users
            $table->boolean('helpful')->default(true);
            $table->timestamps();
            $table->unique(['review_id','user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paket_umrah_review_like_t');
    }
};
