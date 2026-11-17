<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("featured_slots", function (Blueprint $table) {
            $table->id();
            $table->foreignId("school_id")->constrained("schools")->cascadeOnDelete();
            $table->enum("slot_type", ["homepage", "category", "district", "search_top", "article_sidebar"]);
            $table->string("context_type", 60)->nullable();
            $table->unsignedBigInteger("context_id")->nullable();
            $table->timestamp("starts_at");
            $table->timestamp("ends_at");
            $table->boolean("is_active")->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("featured_slots");
    }
};
