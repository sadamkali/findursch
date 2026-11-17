<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("inquiries", function (Blueprint $table) {
            $table->id();
            $table->foreignId("school_id")->constrained("schools")->cascadeOnDelete();
            $table->foreignId("user_id")->nullable()->constrained("users")->nullOnDelete();
            $table->string("parent_name", 255);
            $table->string("parent_email", 255);
            $table->string("parent_phone", 30)->nullable();
            $table->string("student_name", 255)->nullable();
            $table->tinyInteger("student_age")->nullable();
            $table->string("intended_level", 120)->nullable();
            $table->text("message");
            $table->enum("source", ["web", "whatsapp", "api", "admin"])->default("web");
            $table->enum("status", ["new", "read", "responded", "archived"])->default("new");
            $table->text("owner_response")->nullable();
            $table->timestamp("responded_at")->nullable();
            $table->foreignId("responded_by")->nullable()->constrained("users")->nullOnDelete();
            $table->timestamps();

            $table->index("status");
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("inquiries");
    }
};
