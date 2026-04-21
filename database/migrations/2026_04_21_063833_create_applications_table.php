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
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->unique();
            
            // Personal Information
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->date('date_of_birth');
            $table->string('gender');
            $table->string('nationality');
            $table->string('id_number')->nullable();
            $table->string('passport_number')->nullable();
            
            // Contact Information
            $table->string('email');
            $table->string('phone');
            $table->string('alternative_phone')->nullable();
            $table->text('address');
            $table->string('city');
            $table->string('district');
            $table->string('country');
            
            // Programme Information
            $table->string('programme_choice_1');
            $table->string('programme_choice_2')->nullable();
            $table->string('intake_year');
            $table->string('study_mode')->nullable(); // full-time, part-time
            
            // Education Background
            $table->json('education_history')->nullable();
            
            // Employment History (if applicable)
            $table->json('employment_history')->nullable();
            
            // Next of Kin
            $table->string('kin_name')->nullable();
            $table->string('kin_relationship')->nullable();
            $table->string('kin_phone')->nullable();
            $table->string('kin_email')->nullable();
            
            // Additional Information
            $table->text('additional_info')->nullable();
            $table->json('documents')->nullable(); // Uploaded document references
            
            // Application Status
            $table->enum('status', [
                'submitted',
                'under_review',
                'pending_documents',
                'shortlisted',
                'accepted',
                'rejected',
                'withdrawn'
            ])->default('submitted');
            
            $table->timestamp('submitted_at');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('reference_no');
            $table->index('status');
            $table->index('email');
            $table->index('submitted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
