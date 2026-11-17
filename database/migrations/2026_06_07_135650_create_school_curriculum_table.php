<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("school_curriculum", function (Blueprint $table) {
            $table->foreignId("school_id")->constrained("schools")->cascadeOnDelete();
            $table->foreignId("curriculum_id")->constrained("curricula")->cascadeOnDelete();
            $table->primary(["school_id", "curriculum_id"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("school_curriculum");
    }
};
