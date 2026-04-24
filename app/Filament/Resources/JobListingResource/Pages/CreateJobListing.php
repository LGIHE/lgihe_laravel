<?php

namespace App\Filament\Resources\JobListingResource\Pages;

use App\Filament\Resources\JobListingResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateJobListing extends CreateRecord
{
    protected static string $resource = JobListingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // If status is active and no published_at is set, set it to now
        if ($data['status'] === 'active' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }
        
        // Handle document metadata
        $data = $this->handleDocumentMetadata($data);
        
        return $data;
    }

    protected function getCreatedNotification(): ?Notification
    {
        $status = $this->record->status;
        
        return Notification::make()
            ->success()
            ->title('Job listing created')
            ->body($status === 'active' 
                ? 'The job listing is now active and accepting applications.' 
                : 'The job listing has been saved as a draft.')
            ->send();
    }

    protected function handleDocumentMetadata(array $data): array
    {
        if (!empty($data['document_path'])) {
            try {
                $filePath = $data['document_path'];
                
                // Check if it's a Livewire temporary file
                if (is_object($filePath) && method_exists($filePath, 'getSize')) {
                    // It's a Livewire TemporaryUploadedFile
                    $data['document_name'] = $filePath->getClientOriginalName();
                    $data['document_size'] = $filePath->getSize();
                    $data['document_type'] = $filePath->getMimeType();
                } else {
                    // It's a file path string (already stored)
                    $fullPath = storage_path('app/public/' . $filePath);
                    
                    if (file_exists($fullPath)) {
                        $data['document_name'] = basename($filePath);
                        $data['document_size'] = filesize($fullPath);
                        $data['document_type'] = mime_content_type($fullPath);
                    }
                }
            } catch (\Exception $e) {
                // If there's any error getting metadata, just skip it
                // The file will still be uploaded successfully
            }
        }
        
        return $data;
    }
}
