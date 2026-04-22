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
            $table->string('department')->nullable()->after('location');
            $table->string('reports_to')->nullable()->after('department');
            $table->text('supervises_who')->nullable()->after('reports_to');
            $table->string('document_path')->nullable()->after('supervises_who');
            $table->string('document_name')->nullable()->after('document_path');
            $table->string('document_type')->nullable()->after('document_name');
            $table->bigInteger('document_size')->nullable()->after('document_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_listings', function (Blueprint $table) {
            $table->dropColumn([
                'department',
                'reports_to', 
                'supervises_who',
                'document_path',
                'document_name',
                'document_type',
                'document_size'
            ]);
        });
    }
};
