<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('job_listings', function (Blueprint $table) {
            $table->longText('purpose_of_role')->nullable()->after('description');
            $table->longText('core_competencies')->nullable()->after('responsibilities');
            $table->longText('application_requirements')->nullable()->after('requirements');
            $table->longText('application_process')->nullable()->after('application_requirements');
            $table->longText('disclaimer')->nullable()->after('application_process');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_listings', function (Blueprint $table) {
            $table->dropColumn([
                'purpose_of_role',
                'core_competencies',
                'application_requirements',
                'application_process',
                'disclaimer',
            ]);
        });
    }
};
