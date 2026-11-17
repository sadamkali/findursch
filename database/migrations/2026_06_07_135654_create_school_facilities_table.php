<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("school_facilities", function (Blueprint $table) {
            $table->foreignId("school_id")->constrained("schools")->cascadeOnDelete();
            $table->foreignId("facility_id")->constrained("facilities")->cascadeOnDelete();
            $table->string("notes", 255)->nullable();
            $table->primary(["school_id", "facility_id"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("school_facilities");
    }
};
