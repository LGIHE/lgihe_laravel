<?php

namespace App\Filament\Resources\JobListingResource\Pages;

use App\Filament\Resources\JobListingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditJobListing extends EditRecord
{
    protected static string $resource = JobListingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Handle document metadata
        $data = $this->handleDocumentMetadata($data);
        
        return $data;
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
        } elseif (isset($data['document_path']) && empty($data['document_path'])) {
            // Document was removed
            $data['document_name'] = null;
            $data['document_size'] = null;
            $data['document_type'] = null;
        }
        
        return $data;
    }
}
