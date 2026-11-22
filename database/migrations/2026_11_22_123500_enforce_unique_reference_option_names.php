<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Support\\Facades\\DB;

return new class extends Migration
{
    /**
     * Keep registration reference names unique regardless of letter casing
     * or leading/trailing spaces. The same name can still exist in another
     * reference table because each list is managed independently.
     */
    public function up(): void
    {
        DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS school_types_name_normalized_unique ON public.school_types (LOWER(BTRIM(name)))');
        DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS curricula_name_normalized_unique ON public.curricula (LOWER(BTRIM(name)))');
        DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS facilities_name_normalized_unique ON public.facilities (LOWER(BTRIM(name)))');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS public.school_types_name_normalized_unique');
        DB::statement('DROP INDEX IF EXISTS public.curricula_name_normalized_unique');
        DB::statement('DROP INDEX IF EXISTS public.facilities_name_normalized_unique');
    }
};
