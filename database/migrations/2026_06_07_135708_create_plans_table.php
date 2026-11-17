<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("plans", function (Blueprint $table) {
            $table->id();
            $table->string("name", 120);
            $table->string("slug", 140)->unique();
            $table->string("tagline", 255)->nullable();
            $table->text("description")->nullable();
            $table->decimal("price", 12, 2)->default(0);
            $table->string("currency", 10)->default("UGX");
            $table->integer("duration_days");
            $table->enum("billing_cycle", ["monthly", "yearly", "once_off"])->default("yearly");
            $table->json("features")->nullable();
            $table->boolean("includes_featured_slot")->default(false);
            $table->tinyInteger("featured_slot_count")->default(0);
            $table->boolean("is_active")->default(true);
            $table->integer("order")->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("plans");
    }
};
