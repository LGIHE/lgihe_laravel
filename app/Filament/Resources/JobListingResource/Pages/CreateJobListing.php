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

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label('Save as Draft')
                ->action(function () {
                    $data = $this->form->getState();
                    $data['status'] = 'draft';
                    $data['published_at'] = null;
                    
                    // Handle document metadata
                    $data = $this->handleDocumentMetadata($data);
                    
                    $this->data = $data;
                    $this->create();
                }),
            
            Actions\Action::make('publish')
                ->label('Publish Now')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->modalHeading('Publish Job Listing')
                ->modalDescription('Are you sure you want to publish this job listing? It will be immediately visible and accepting applications.')
                ->modalSubmitActionLabel('Yes, Publish')
                ->action(function () {
                    $data = $this->form->getState();
                    $data['status'] = 'active';
                    $data['published_at'] = now();
                    
                    // Handle document metadata
                    $data = $this->handleDocumentMetadata($data);
                    
                    $this->data = $data;
                    $this->create();
                }),
        ];
    }

    protected function handleDocumentMetadata(array $data): array
    {
        if (!empty($data['document_path'])) {
            $filePath = $data['document_path'];
            $fullPath = storage_path('app/public/' . $filePath);
            
            if (file_exists($fullPath)) {
                $data['document_name'] = basename($filePath);
                $data['document_size'] = filesize($fullPath);
                $data['document_type'] = mime_content_type($fullPath);
            }
        }
        
        return $data;
    }
}
