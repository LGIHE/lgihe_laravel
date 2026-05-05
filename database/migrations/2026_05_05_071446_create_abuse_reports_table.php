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
        Schema::create('abuse_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_id', 50)->unique();
            
            // Reporter information (optional for anonymous reports)
            $table->string('reporter_name')->nullable();
            $table->string('reporter_email')->nullable();
            $table->string('reporter_phone', 50)->nullable();
            $table->string('reporter_relationship', 50)->nullable();
            $table->string('preferred_contact', 20)->nullable();
            $table->boolean('anonymous_report')->default(false);
            
            // Incident details (required)
            $table->string('incident_type', 50);
            $table->date('incident_date');
            $table->text('incident_location');
            $table->text('persons_involved');
            $table->text('detailed_description');
            
            // Additional details (optional)
            $table->text('witnesses_present')->nullable();
            $table->text('previously_reported')->nullable();
            $table->text('evidence_available')->nullable();
            
            // Status tracking
            $table->string('status', 20)->default('pending');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('report_id');
            $table->index('incident_type');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('abuse_reports');
    }
};
