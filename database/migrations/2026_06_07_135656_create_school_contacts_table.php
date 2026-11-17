<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("school_contacts", function (Blueprint $table) {
            $table->id();
            $table->foreignId("school_id")->constrained("schools")->cascadeOnDelete();
            $table->enum("type", ["phone", "email", "whatsapp", "facebook", "twitter", "instagram", "website", "other"]);
            $table->string("value", 255);
            $table->string("label", 120)->nullable();
            $table->boolean("is_primary")->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("school_contacts");
    }
};
