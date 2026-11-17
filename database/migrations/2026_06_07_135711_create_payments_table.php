<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("payments", function (Blueprint $table) {
            $table->id();
            $table->foreignId("school_id")->constrained("schools")->cascadeOnDelete();
            $table->foreignId("subscription_id")->nullable()->constrained("subscriptions")->nullOnDelete();
            $table->decimal("amount", 12, 2);
            $table->string("currency", 10)->default("UGX");
            $table->string("payment_method", 60)->nullable();
            $table->string("transaction_id", 255)->nullable()->unique();
            $table->enum("status", ["pending", "completed", "failed", "refunded"])->default("pending");
            $table->string("gateway", 60)->nullable();
            $table->json("gateway_response")->nullable();
            $table->timestamp("paid_at")->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("payments");
    }
};
