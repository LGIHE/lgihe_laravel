<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserProfile extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static string $view = 'filament.pages.user-profile';

    protected static ?string $title = 'My Profile';

    protected static ?string $navigationLabel = 'My Profile';

    protected static ?int $navigationSort = 100;

    public ?array $profileData = [];
    public ?array $passwordData = [];

    public function mount(): void
    {
        $user = auth()->user();
        $this->profileData = [
            'name' => $user->name,
            'email' => $user->email,
        ];
        
        $this->passwordData = [
            'current_password' => '',
            'password' => '',
            'password_confirmation' => '',
        ];
    }

    public function profileForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Profile Information')
                    ->description('Update your account profile information.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique('users', 'email', auth()->id())
                            ->maxLength(255),
                    ])
                    ->columns(2),
            ])
            ->statePath('profileData');
    }

    public function passwordForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Update Password')
                    ->description('Ensure your account is using a long, random password to stay secure.')
                    ->schema([
                        Forms\Components\TextInput::make('current_password')
                            ->label('Current Password')
                            ->password()
                            ->required()
                            ->currentPassword(),
                        Forms\Components\TextInput::make('password')
                            ->label('New Password')
                            ->password()
                            ->required()
                            ->rule(Password::default())
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->live(debounce: 500)
                            ->same('password_confirmation'),
                        Forms\Components\TextInput::make('password_confirmation')
                            ->label('Confirm Password')
                            ->password()
                            ->required()
                            ->dehydrated(false),
                    ])
                    ->columns(1),
            ])
            ->statePath('passwordData');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('updateProfile')
                ->label('Update Profile')
                ->color('primary')
                ->action('updateProfile'),
            Action::make('updatePassword')
                ->label('Update Password')
                ->color('warning')
                ->action('updatePassword'),
        ];
    }

    public function updateProfile(): void
    {
        $data = $this->profileForm->getState();

        auth()->user()->update([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        Notification::make()
            ->title('Profile updated successfully')
            ->success()
            ->send();
    }

    public function updatePassword(): void
    {
        $data = $this->passwordForm->getState();

        // Verify current password
        if (!Hash::check($data['current_password'], auth()->user()->password)) {
            Notification::make()
                ->title('Current password is incorrect')
                ->danger()
                ->send();
            return;
        }

        auth()->user()->update([
            'password' => Hash::make($data['password']),
        ]);

        // Clear password fields
        $this->passwordData = [
            'current_password' => '',
            'password' => '',
            'password_confirmation' => '',
        ];

        Notification::make()
            ->title('Password updated successfully')
            ->success()
            ->send();
    }

    public static function canAccess(): bool
    {
        return auth()->check();
    }
}
