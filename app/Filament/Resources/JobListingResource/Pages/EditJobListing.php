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
        if (!empty($data['document_path'])) {
            $filePath = $data['document_path'];
            $fullPath = storage_path('app/public/' . $filePath);
            
            if (file_exists($fullPath)) {
                $data['document_name'] = basename($filePath);
                $data['document_size'] = filesize($fullPath);
                $data['document_type'] = mime_content_type($fullPath);
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
