<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_listings', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('employer');
            $table->string('category', 30);
            $table->string('location', 180);
            $table->string('employment_type', 30);
            $table->string('excerpt', 600)->nullable();
            $table->longText('description');
            $table->string('salary', 180)->nullable();
            $table->date('deadline')->nullable();
            $table->string('application_url', 2048)->nullable();
            $table->string('application_email')->nullable();
            $table->text('application_instructions')->nullable();
            $table->string('status', 30)->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'published_at', 'is_featured', 'created_at'], 'job_listings_public_listing_idx');
            $table->index(['category', 'status', 'published_at'], 'job_listings_category_listing_idx');
            $table->index('deadline', 'job_listings_deadline_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_listings');
    }
};
