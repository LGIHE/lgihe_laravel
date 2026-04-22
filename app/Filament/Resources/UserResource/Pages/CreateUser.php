<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Notifications\UserCreatedNotification;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /**
     * Mutate form data before creating the user.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // If no password is provided, generate a random one
        // The user will set their own password via email link
        if (empty($data['password'])) {
            $data['password'] = Hash::make(Str::random(32));
        }

        return $data;
    }

    /**
     * Handle after user creation.
     */
    protected function afterCreate(): void
    {
        // Check if user has at least one role assigned
        $hasRoles = $this->record->roles()->count() > 0;
        
        // Send password setup email to the newly created user
        try {
            $this->record->notify(new UserCreatedNotification());
            
            $notification = Notification::make()
                ->title('User created successfully')
                ->body('A password setup email has been sent to ' . $this->record->email)
                ->success();
            
            // Warn if no roles assigned
            if (!$hasRoles) {
                $notification
                    ->title('User created with warning')
                    ->body('Password setup email sent to ' . $this->record->email . '. Note: User has no roles assigned and may not be able to access the admin panel.')
                    ->warning();
            }
            
            $notification->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('User created but email failed')
                ->body('The user was created but the email could not be sent: ' . $e->getMessage())
                ->warning()
                ->send();
        }
    }
}
