<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 30);
            $table->string('title', 255);
            $table->text('description');
            $table->string('venue', 255)->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->date('deadline')->nullable();
            $table->string('award', 180)->nullable();
            $table->string('status', 30)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'status', 'published_at'], 'school_posts_public_listing_idx');
            $table->index(['school_id', 'type', 'created_at'], 'school_posts_school_type_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_posts');
    }
};
