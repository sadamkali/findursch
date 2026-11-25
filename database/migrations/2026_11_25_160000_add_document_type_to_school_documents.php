<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_documents', function (Blueprint $table) {
            $table->string('document_type', 32)->default('requirement')->after('title');
            $table->index(
                ['school_id', 'document_type', 'created_at'],
                'school_docs_type_created_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('school_documents', function (Blueprint $table) {
            $table->dropIndex('school_docs_type_created_idx');
            $table->dropColumn('document_type');
        });
    }
};
