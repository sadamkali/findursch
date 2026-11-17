<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("reviews", function (Blueprint $table) {
            $table->id();
            $table->foreignId("school_id")->constrained("schools")->cascadeOnDelete();
            $table->foreignId("user_id")->constrained("users")->cascadeOnDelete();
            $table->enum("reviewer_type", ["parent", "student", "alumni", "staff", "other"]);
            $table->tinyInteger("rating")->unsigned();
            $table->string("title", 255);
            $table->text("body");
            $table->tinyInteger("academic_rating")->unsigned()->nullable();
            $table->tinyInteger("facilities_rating")->unsigned()->nullable();
            $table->tinyInteger("staff_rating")->unsigned()->nullable();
            $table->tinyInteger("environment_rating")->unsigned()->nullable();
            $table->enum("status", ["pending", "approved", "rejected", "flagged"])->default("pending");
            $table->boolean("is_verified")->default(false);
            $table->text("owner_response")->nullable();
            $table->timestamp("owner_responded_at")->nullable();
            $table->integer("helpful_count")->default(0);
            $table->foreignId("moderated_by")->nullable()->constrained("users")->nullOnDelete();
            $table->timestamp("moderated_at")->nullable();
            $table->timestamps();

            $table->index("status");
            $table->unique(["school_id", "user_id"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("reviews");
    }
};
