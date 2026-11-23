<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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

        DB::statement('ALTER TABLE public.school_documents ENABLE ROW LEVEL SECURITY');
        DB::statement('CREATE POLICY school_documents_deny_client_access ON public.school_documents FOR ALL TO anon, authenticated USING (false) WITH CHECK (false)');
    }

    public function down(): void
    {
        Schema::dropIfExists('school_documents');
    }
};
