<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->boolean('admissions_open')->default(false);
            $table->string('admission_classes', 500)->nullable();
            $table->date('admission_deadline')->nullable();
            $table->string('application_fee', 120)->nullable();
            $table->string('admissions_contact', 180)->nullable();
            $table->text('admission_requirements')->nullable();
            $table->text('how_to_apply')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn([
                'admissions_open',
                'admission_classes',
                'admission_deadline',
                'application_fee',
                'admissions_contact',
                'admission_requirements',
                'how_to_apply',
            ]);
        });
    }
};
