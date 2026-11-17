<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("schools", function (Blueprint $table) {
            $table->id();
            $table->foreignId("owner_id")->nullable()->constrained("users")->nullOnDelete();
            $table->foreignId("type_id")->constrained("school_types")->cascadeOnDelete();
            $table->foreignId("district_id")->constrained("districts")->cascadeOnDelete();
            $table->foreignId("sub_county_id")->nullable()->constrained("sub_counties")->nullOnDelete();
            $table->string("name", 255);
            $table->string("slug", 280)->unique();
            $table->string("tagline", 255)->nullable();
            $table->text("description")->nullable();
            $table->year("founded_year")->nullable();
            $table->string("registration_number", 60)->nullable();
            $table->decimal("latitude", 10, 8)->nullable();
            $table->decimal("longitude", 11, 8)->nullable();
            $table->text("address")->nullable();
            $table->string("phone", 30)->nullable();
            $table->string("email", 255)->nullable();
            $table->string("website", 255)->nullable();
            $table->string("whatsapp", 30)->nullable();
            $table->enum("boarding_type", ["boarding", "day", "mixed"])->default("day");
            $table->enum("gender_policy", ["boys", "girls", "mixed"])->default("mixed");
            $table->integer("student_count_approx")->nullable();
            $table->enum("status", ["draft", "pending", "active", "suspended", "rejected"])->default("draft");
            $table->enum("verification_status", ["unverified", "pending", "verified"])->default("unverified");
            $table->timestamp("verified_at")->nullable();
            $table->foreignId("verified_by")->nullable()->constrained("users")->nullOnDelete();
            $table->boolean("is_featured")->default(false);
            $table->date("featured_until")->nullable();
            $table->integer("views_count")->default(0);
            $table->integer("inquiries_count")->default(0);
            $table->decimal("rating_average", 3, 2)->default(0);
            $table->integer("reviews_count")->default(0);
            $table->string("meta_title", 255)->nullable();
            $table->text("meta_description")->nullable();
            $table->text("rejection_reason")->nullable();
            $table->timestamp("submitted_at")->nullable();
            $table->timestamps();

            $table->index(["status", "verification_status"]);
            $table->index(["is_featured"]);
            $table->index(["district_id", "status"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("schools");
    }
};
