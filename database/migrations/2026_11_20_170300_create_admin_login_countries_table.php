<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_login_countries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->char('country_code', 2);
            $table->timestamp('first_seen_at');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'country_code']);
            $table->index(['user_id', 'approved_at']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE public.admin_login_countries ENABLE ROW LEVEL SECURITY');
            DB::statement('REVOKE ALL ON TABLE public.admin_login_countries FROM anon, authenticated');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_login_countries');
    }
};
