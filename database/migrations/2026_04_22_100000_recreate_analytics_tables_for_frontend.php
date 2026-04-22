<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Backup existing data
        $events = DB::table('analytics_events')->get();
        $errors = DB::table('analytics_errors')->get();
        $pageLoads = DB::table('page_loads')->get();

        // Drop and recreate analytics_events
        Schema::dropIfExists('analytics_events');
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('properties')->nullable();
            $table->string('session_id')->nullable()->index();
            $table->text('user_agent')->nullable();
            $table->text('referrer')->nullable();
            $table->string('screen_resolution', 50)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('country_code', 10)->nullable();
            $table->string('city', 100)->nullable();
            $table->timestamp('timestamp');
            $table->timestamps();
            
            $table->index('name');
            $table->index('country_code');
            $table->index('timestamp');
        });

        // Drop and recreate analytics_errors
        Schema::dropIfExists('analytics_errors');
        Schema::create('analytics_errors', function (Blueprint $table) {
            $table->id();
            $table->text('message');
            $table->text('stack')->nullable();
            $table->text('url')->nullable();
            $table->text('user_agent')->nullable();
            $table->enum('severity', ['low', 'medium', 'high', 'critical']);
            $table->timestamp('timestamp');
            $table->timestamps();
            
            $table->index('severity');
            $table->index('timestamp');
        });

        // Drop and recreate page_loads
        Schema::dropIfExists('page_loads');
        Schema::create('page_loads', function (Blueprint $table) {
            $table->id();
            $table->string('url', 500);
            $table->integer('load_time');
            $table->string('session_id')->nullable()->index();
            $table->text('user_agent')->nullable();
            $table->string('country', 100)->nullable();
            $table->string('country_code', 10)->nullable();
            $table->string('city', 100)->nullable();
            $table->timestamp('timestamp');
            $table->timestamps();
            
            $table->index('url');
            $table->index('timestamp');
            $table->index('load_time');
        });

        // Migrate old data if any exists
        foreach ($events as $event) {
            DB::table('analytics_events')->insert([
                'name' => $event->event_name ?? $event->event_type ?? 'unknown',
                'properties' => $event->event_data ?? null,
                'session_id' => $event->session_id ?? null,
                'user_agent' => $event->user_agent ?? null,
                'referrer' => $event->referrer ?? null,
                'timestamp' => $event->created_at ?? now(),
                'created_at' => $event->created_at ?? now(),
                'updated_at' => $event->updated_at ?? now(),
            ]);
        }

        foreach ($errors as $error) {
            DB::table('analytics_errors')->insert([
                'message' => $error->error_message ?? 'Unknown error',
                'stack' => $error->stack_trace ?? null,
                'url' => $error->page_url ?? null,
                'user_agent' => $error->user_agent ?? null,
                'severity' => 'medium', // Default severity for old errors
                'timestamp' => $error->created_at ?? now(),
                'created_at' => $error->created_at ?? now(),
                'updated_at' => $error->updated_at ?? now(),
            ]);
        }

        foreach ($pageLoads as $pageLoad) {
            DB::table('page_loads')->insert([
                'url' => $pageLoad->page_url ?? '/',
                'load_time' => $pageLoad->load_time ?? 0,
                'session_id' => $pageLoad->session_id ?? null,
                'user_agent' => $pageLoad->user_agent ?? null,
                'timestamp' => $pageLoad->created_at ?? now(),
                'created_at' => $pageLoad->created_at ?? now(),
                'updated_at' => $pageLoad->updated_at ?? now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop new tables
        Schema::dropIfExists('analytics_events');
        Schema::dropIfExists('analytics_errors');
        Schema::dropIfExists('page_loads');

        // Recreate old schema
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->index();
            $table->string('event_type');
            $table->string('event_name')->nullable();
            $table->json('event_data')->nullable();
            $table->string('page_url');
            $table->string('referrer')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
            
            $table->index('event_type');
            $table->index('created_at');
        });

        Schema::create('analytics_errors', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->index();
            $table->string('error_type');
            $table->text('error_message');
            $table->text('stack_trace')->nullable();
            $table->string('page_url');
            $table->string('user_agent')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
            
            $table->index('error_type');
            $table->index('created_at');
        });

        Schema::create('page_loads', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->index();
            $table->string('page_url');
            $table->string('page_title')->nullable();
            $table->string('referrer')->nullable();
            $table->integer('load_time')->nullable();
            $table->string('device_type')->nullable();
            $table->string('browser')->nullable();
            $table->string('os')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
            
            $table->index('created_at');
        });
    }
};
