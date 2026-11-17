<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("subscriptions", function (Blueprint $table) {
            $table->id();
            $table->foreignId("school_id")->constrained("schools")->cascadeOnDelete();
            $table->foreignId("plan_id")->constrained("plans")->cascadeOnDelete();
            $table->date("start_date");
            $table->date("end_date");
            $table->enum("status", ["active", "expired", "cancelled", "pending"])->default("pending");
            $table->boolean("auto_renew")->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("subscriptions");
    }
};
