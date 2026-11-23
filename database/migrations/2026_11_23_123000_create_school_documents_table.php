<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title', 180);
            $table->text('description')->nullable();
            $table->string('original_filename', 255);
            $table->string('stored_path', 500)->unique();
            $table->string('mime_type', 180);
            $table->unsignedBigInteger('file_size');
            $table->timestamps();
            $table->index(['school_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_documents');
    }
};
