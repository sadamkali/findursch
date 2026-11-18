<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title', 180);
            $table->string('youtube_url', 500);
            $table->string('youtube_video_id', 30);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
            $table->index(['school_id', 'status']);
        });

        Schema::create('school_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('type', ['event', 'news', 'bursary']);
            $table->string('title', 255);
            $table->text('description');
            $table->string('venue', 255)->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->date('deadline')->nullable();
            $table->string('award', 180)->nullable();
            $table->enum('status', ['draft', 'pending', 'approved', 'published', 'rejected'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->index(['school_id', 'type', 'status']);
        });

        Schema::create('school_advertisements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title', 180);
            $table->enum('placement', ['school_spotlight', 'homepage_banner', 'search_feature'])->default('school_spotlight');
            $table->string('destination_url', 500)->nullable();
            $table->text('description');
            $table->enum('status', ['draft', 'pending', 'active', 'rejected', 'expired'])->default('draft');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
            $table->index(['school_id', 'status']);
        });

        Schema::create('school_message_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name', 180)->nullable();
            $table->enum('channel', ['whatsapp', 'sms', 'email']);
            $table->string('audience', 40)->default('manual');
            $table->json('recipients')->nullable();
            $table->text('message');
            $table->enum('status', ['draft', 'queued', 'sent', 'failed'])->default('draft');
            $table->string('provider_reference', 180)->nullable();
            $table->text('provider_error')->nullable();
            $table->timestamps();
            $table->index(['school_id', 'channel', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_message_campaigns');
        Schema::dropIfExists('school_advertisements');
        Schema::dropIfExists('school_posts');
        Schema::dropIfExists('school_videos');
    }
};
