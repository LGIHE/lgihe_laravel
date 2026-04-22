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
                ->action(function (array $data) {
                    $data['status'] = 'draft';
                    $data['published_at'] = null;
                    $this->create($data);
                }),
            
            Actions\Action::make('publish')
                ->label('Publish Now')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->modalHeading('Publish Job Listing')
                ->modalDescription('Are you sure you want to publish this job listing? It will be immediately visible and accepting applications.')
                ->modalSubmitActionLabel('Yes, Publish')
                ->action(function (array $data) {
                    $data['status'] = 'active';
                    $data['published_at'] = now();
                    $this->create($data);
                }),
        ];
    }
}
