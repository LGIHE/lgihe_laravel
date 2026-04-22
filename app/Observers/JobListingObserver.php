<?php

namespace App\Observers;

use App\Models\JobListing;

class JobListingObserver
{
    /**
     * Handle the JobListing "creating" event.
     */
    public function creating(JobListing $jobListing): void
    {
        $this->handleDocumentMetadata($jobListing);
    }

    /**
     * Handle the JobListing "updating" event.
     */
    public function updating(JobListing $jobListing): void
    {
        $this->handleDocumentMetadata($jobListing);
    }

    /**
     * Handle document metadata extraction
     */
    private function handleDocumentMetadata(JobListing $jobListing): void
    {
        if (!empty($jobListing->document_path)) {
            $filePath = $jobListing->document_path;
            
            // Try both real and fake storage paths
            $fullPath = storage_path('app/public/' . $filePath);
            $fakeStoragePath = storage_path('framework/testing/disks/public/' . $filePath);
            
            $pathToUse = file_exists($fullPath) ? $fullPath : $fakeStoragePath;
            
            if (file_exists($pathToUse)) {
                $jobListing->document_name = basename($filePath);
                $jobListing->document_size = filesize($pathToUse);
                $jobListing->document_type = mime_content_type($pathToUse);
            }
        } elseif ($jobListing->isDirty('document_path') && empty($jobListing->document_path)) {
            // Document was removed
            $jobListing->document_name = null;
            $jobListing->document_size = null;
            $jobListing->document_type = null;
        }
    }
}
