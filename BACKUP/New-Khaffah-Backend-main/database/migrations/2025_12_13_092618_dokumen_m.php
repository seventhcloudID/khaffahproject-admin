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
        Schema::create('dokumen_m', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(true);
            // ownership
            $table->foreignId('tipe_owner_id')
            ->constrained('tipe_owner_m');
            
            $table->unsignedBigInteger('owner_id');
            
            // document type
            $table->foreignId('tipe_dokumen_id')
            ->constrained('tipe_dokumen_m');
            
            // file data
            $table->string('file_path');
            $table->string('file_hash', 64);
            $table->string('mime_type', 50);
            
            $table->foreignId('status_id')->constrained('status_m');
            
            $table->foreignId('uploaded_by')
            ->nullable()
            ->constrained('users')
            ->nullOnDelete();
            
            $table->foreignId('verified_by')
            ->nullable()
            ->constrained('users')
            ->nullOnDelete();
            
            $table->timestamp('verified_at')->nullable();
            $table->text('alasan_ditolak')->nullable();
            
            $table->json('extra_data')->nullable();
            
            $table->timestamp('superseded_at')->nullable();
            $table->timestamps();

            $table->index(['tipe_owner_id', 'owner_id']);
            $table->index(['tipe_dokumen_id', 'status_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dokumen_m');
    }
};
