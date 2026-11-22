<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function ($table) {
            $table->boolean('owner_invitation_pending')->default(false);
        });

        if (DB::table('schools')->whereNull('owner_id')->exists()) {
            throw new RuntimeException(
                'Cannot require school owner accounts yet: assign an owner account to every existing school before applying this migration.'
            );
        }

        DB::statement('ALTER TABLE schools ALTER COLUMN owner_id SET NOT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE schools ALTER COLUMN owner_id DROP NOT NULL');
        Schema::table('users', function ($table) {
            $table->dropColumn('owner_invitation_pending');
        });
    }
};
