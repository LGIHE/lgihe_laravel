<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\NewsController;
use App\Http\Controllers\Api\V1\JobListingController;
use App\Http\Controllers\Api\V1\EventController;
use App\Http\Controllers\Api\V1\TenderController;
use App\Http\Controllers\Api\V1\ResearchController;
use App\Http\Controllers\Api\V1\ContactController;
use App\Http\Controllers\Api\V1\ApplicationController;
use App\Http\Controllers\Api\V1\AnalyticsController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\PasswordSetupController;
use App\Http\Controllers\Api\V1\Admin\NewsAdminController;
use App\Http\Controllers\Api\V1\Admin\ApplicationAdminController;
use App\Http\Controllers\Api\V1\Admin\ContactInquiryAdminController;

// Public API endpoints - v1
Route::prefix('v1')->group(function () {
    
    // Content endpoints
    Route::get('news', [NewsController::class, 'index']);
    Route::get('news/{slug}', [NewsController::class, 'show']);
    
    Route::get('jobs', [JobListingController::class, 'index']);
    Route::get('jobs/{id}', [JobListingController::class, 'show']);
    Route::get('jobs/{jobListing}/download-document', [JobListingController::class, 'downloadDocument'])
        ->name('job-listing.download-document');
    
    Route::get('events', [EventController::class, 'index']);
    Route::get('events/{id}', [EventController::class, 'show']);
    
    Route::get('tenders', [TenderController::class, 'index']);
    Route::get('tenders/{id}', [TenderController::class, 'show']);
    
    Route::get('research', [ResearchController::class, 'index']);
    Route::get('research/{id}', [ResearchController::class, 'show']);
    
    // Form submissions
    Route::post('contact', [ContactController::class, 'store']);
    Route::post('applications', [ApplicationController::class, 'store']);
    
    // Analytics endpoints
    Route::post('analytics/event', [AnalyticsController::class, 'storeEvent']);
    Route::post('analytics/error', [AnalyticsController::class, 'storeError']);
    Route::post('analytics/pageload', [AnalyticsController::class, 'storePageLoad']);
    
    // Authentication
    Route::post('auth/login', [AuthController::class, 'login']);
    
    // Password setup (public endpoints)
    Route::post('password/verify-token', [PasswordSetupController::class, 'verifyToken']);
    Route::post('password/setup', [PasswordSetupController::class, 'setupPassword']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me', [AuthController::class, 'me']);
        
        // Profile management
        Route::get('profile', [App\Http\Controllers\Api\V1\ProfileController::class, 'show']);
        Route::put('profile', [App\Http\Controllers\Api\V1\ProfileController::class, 'update']);
        Route::post('profile/change-password', [App\Http\Controllers\Api\V1\ProfileController::class, 'changePassword']);
        Route::delete('profile', [App\Http\Controllers\Api\V1\ProfileController::class, 'destroy']);
    });
    
    // Admin endpoints (protected by Sanctum)
    Route::prefix('admin')->middleware('auth:sanctum')->group(function () {
        
        // News management
        Route::get('news', [NewsAdminController::class, 'index']);
        Route::post('news', [NewsAdminController::class, 'store']);
        Route::get('news/{id}', [NewsAdminController::class, 'show']);
        Route::put('news/{id}', [NewsAdminController::class, 'update']);
        Route::delete('news/{id}', [NewsAdminController::class, 'destroy']);
        
        // Application management
        Route::get('applications', [ApplicationAdminController::class, 'index']);
        Route::get('applications/{id}', [ApplicationAdminController::class, 'show']);
        Route::put('applications/{id}/status', [ApplicationAdminController::class, 'updateStatus']);
        Route::post('applications/{id}/notes', [ApplicationAdminController::class, 'addNote']);
        
        // Contact inquiry management
        Route::get('inquiries', [ContactInquiryAdminController::class, 'index']);
        Route::get('inquiries/{id}', [ContactInquiryAdminController::class, 'show']);
        Route::put('inquiries/{id}/status', [ContactInquiryAdminController::class, 'updateStatus']);
        Route::delete('inquiries/{id}', [ContactInquiryAdminController::class, 'destroy']);
        
        // TODO: Add similar routes for Jobs, Events, Tenders, Research
    });
});
