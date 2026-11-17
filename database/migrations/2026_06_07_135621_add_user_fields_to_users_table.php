<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table("users", function (Blueprint $table) {
            $table->enum("user_type", ["admin", "school_owner", "parent", "student"])->default("parent")->after("password");
            $table->enum("status", ["active", "inactive", "suspended", "banned"])->default("active")->after("user_type");
            $table->string("profile_photo", 255)->nullable()->after("status");
            $table->string("phone", 30)->nullable()->after("profile_photo");
            $table->foreignId("district_id")->nullable()->after("phone")->constrained()->nullOnDelete();
            $table->timestamp("last_login_at")->nullable()->after("district_id");
        });
    }

    public function down(): void
    {
        Schema::table("users", function (Blueprint $table) {
            $table->dropColumn("last_login_at");
            $table->dropConstrainedForeignId("district_id");
            $table->dropColumn("phone");
            $table->dropColumn("profile_photo");
            $table->dropColumn("status");
            $table->dropColumn("user_type");
        });
    }
};
