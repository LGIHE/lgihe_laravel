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
        Schema::table('tenders', function (Blueprint $table) {
            // Remove old document_url field
            $table->dropColumn('document_url');
            
            // Add RFP document fields
            $table->string('rfp_document_path')->nullable()->after('closing_date');
            $table->string('rfp_document_name')->nullable()->after('rfp_document_path');
            $table->string('rfp_document_type')->nullable()->after('rfp_document_name');
            $table->unsignedBigInteger('rfp_document_size')->nullable()->after('rfp_document_type');
            
            // Add ToR document fields
            $table->string('tor_document_path')->nullable()->after('rfp_document_size');
            $table->string('tor_document_name')->nullable()->after('tor_document_path');
            $table->string('tor_document_type')->nullable()->after('tor_document_name');
            $table->unsignedBigInteger('tor_document_size')->nullable()->after('tor_document_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenders', function (Blueprint $table) {
            $table->dropColumn([
                'rfp_document_path',
                'rfp_document_name',
                'rfp_document_type',
                'rfp_document_size',
                'tor_document_path',
                'tor_document_name',
                'tor_document_type',
                'tor_document_size',
            ]);
            
            $table->string('document_url')->nullable()->after('closing_date');
        });
    }
};
