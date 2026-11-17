<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("articles", function (Blueprint $table) {
            $table->id();
            $table->foreignId("author_id")->nullable()->constrained("users")->nullOnDelete();
            $table->foreignId("category_id")->nullable()->constrained("article_categories")->nullOnDelete();
            $table->string("title", 255);
            $table->string("slug", 280)->unique();
            $table->text("excerpt")->nullable();
            $table->longText("body");
            $table->string("cover_image", 255)->nullable();
            $table->tinyInteger("read_time_minutes")->default(3);
            $table->enum("status", ["draft", "published", "archived"])->default("draft");
            $table->boolean("is_featured")->default(false);
            $table->integer("views_count")->default(0);
            $table->timestamp("published_at")->nullable();
            $table->string("meta_title", 255)->nullable();
            $table->text("meta_description")->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("articles");
    }
};
