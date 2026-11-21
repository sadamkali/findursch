<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_posts', function (Blueprint $table) {
            if (! Schema::hasColumn('school_posts', 'image_url')) {
                $table->text('image_url')->nullable();
            }

            if (! Schema::hasColumn('school_posts', 'image_alt')) {
                $table->string('image_alt', 255)->nullable();
            }
        });
    }

    public function down(): void
    {
        $columns = array_values(array_filter(
            ['image_url', 'image_alt'],
            fn (string $column) => Schema::hasColumn('school_posts', $column)
        ));

        if ($columns !== []) {
            Schema::table('school_posts', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};
