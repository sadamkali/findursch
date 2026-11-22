<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'owner_invitation_pending')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('owner_invitation_pending')->default(false);
            });
        }

        if (DB::table('schools')->whereNull('owner_id')->exists()) {
            throw new RuntimeException(
                'Cannot require school owner accounts yet: assign an owner account to every existing school before applying this migration.'
            );
        }

        $ownerColumn = DB::selectOne(
            "SELECT is_nullable FROM information_schema.columns WHERE table_schema = 'public' AND table_name = 'schools' AND column_name = 'owner_id'"
        );

        if ($ownerColumn?->is_nullable === 'YES') {
            DB::statement('ALTER TABLE public.schools ALTER COLUMN owner_id SET NOT NULL');
        }
    }

    public function down(): void
    {
        $ownerColumn = DB::selectOne(
            "SELECT is_nullable FROM information_schema.columns WHERE table_schema = 'public' AND table_name = 'schools' AND column_name = 'owner_id'"
        );

        if ($ownerColumn?->is_nullable === 'NO') {
            DB::statement('ALTER TABLE public.schools ALTER COLUMN owner_id DROP NOT NULL');
        }

        if (Schema::hasColumn('users', 'owner_invitation_pending')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('owner_invitation_pending');
            });
        }
    }
};
