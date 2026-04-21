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
        Schema::create('analytics_errors', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->index();
            $table->string('error_type'); // javascript, network, etc.
            $table->text('error_message');
            $table->text('stack_trace')->nullable();
            $table->string('page_url');
            $table->string('user_agent')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
            
            $table->index('error_type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_errors');
    }
};
