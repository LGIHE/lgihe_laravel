<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateEvent extends CreateRecord
{
    protected static string $resource = EventResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // If status is published and no published_at is set, set it to now
        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }
        
        return $data;
    }

    protected function getCreatedNotification(): ?Notification
    {
        $status = $this->record->status;
        
        return Notification::make()
            ->success()
            ->title('Event created')
            ->body($status === 'published' 
                ? 'The event has been published and is now visible.' 
                : 'The event has been saved as a draft.')
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
                ->modalHeading('Publish Event')
                ->modalDescription('Are you sure you want to publish this event? It will be immediately visible to the public.')
                ->modalSubmitActionLabel('Yes, Publish')
                ->action(function (array $data) {
                    $data['status'] = 'published';
                    $data['published_at'] = now();
                    $this->create($data);
                }),
        ];
    }
}
