<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("school_fees", function (Blueprint $table) {
            $table->id();
            $table->foreignId("school_id")->constrained("schools")->cascadeOnDelete();
            $table->foreignId("program_id")->nullable()->constrained("school_programs")->nullOnDelete();
            $table->enum("category", ["tuition", "boarding", "uniform", "meals", "activities", "other"]);
            $table->string("label", 180);
            $table->decimal("amount", 12, 2);
            $table->string("currency", 10)->default("UGX");
            $table->enum("period", ["per_term", "per_year", "once_off", "per_month"])->default("per_term");
            $table->string("academic_year", 20)->nullable();
            $table->text("notes")->nullable();
            $table->boolean("is_active")->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("school_fees");
    }
};
