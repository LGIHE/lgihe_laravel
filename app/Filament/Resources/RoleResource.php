<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Role Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->label('Role Name')
                            ->helperText('Enter a unique name for this role (e.g., Editor, Manager)'),
                        
                        Forms\Components\Textarea::make('description')
                            ->maxLength(500)
                            ->rows(3)
                            ->label('Description')
                            ->helperText('Optional description of what this role can do'),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Permissions')
                    ->schema([
                        Forms\Components\CheckboxList::make('permissions')
                            ->relationship('permissions', 'name')
                            ->columns(3)
                            ->gridDirection('row')
                            ->bulkToggleable()
                            ->searchable()
                            ->label('Select Permissions')
                            ->helperText('Choose what actions this role can perform')
                            ->options(function () {
                                return Permission::all()->groupBy(function ($permission) {
                                    // Group by resource (e.g., "view_users" -> "Users")
                                    $parts = explode('_', $permission->name);
                                    return count($parts) > 1 ? ucfirst($parts[1]) : 'Other';
                                })->map(function ($group) {
                                    return $group->pluck('name', 'id');
                                })->flatten();
                            })
                            ->descriptions(function () {
                                return Permission::all()->mapWithKeys(function ($permission) {
                                    return [$permission->id => self::getPermissionDescription($permission->name)];
                                });
                            }),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->label('Role Name'),
                
                Tables\Columns\TextColumn::make('permissions_count')
                    ->counts('permissions')
                    ->label('Permissions')
                    ->badge()
                    ->color('success'),
                
                Tables\Columns\TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Users')
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Role $record) {
                        // Prevent deletion if role has users
                        if ($record->users()->count() > 0) {
                            throw new \Exception('Cannot delete role that has assigned users. Please reassign users first.');
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                if ($record->users()->count() > 0) {
                                    throw new \Exception('Cannot delete roles that have assigned users.');
                                }
                            }
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
            'view' => Pages\ViewRole::route('/{record}'),
        ];
    }

    protected static function getPermissionDescription(string $permissionName): string
    {
        $descriptions = [
            'view' => 'View and list records',
            'create' => 'Create new records',
            'update' => 'Edit existing records',
            'delete' => 'Delete records',
            'restore' => 'Restore deleted records',
            'force_delete' => 'Permanently delete records',
        ];

        foreach ($descriptions as $action => $description) {
            if (str_starts_with($permissionName, $action . '_')) {
                return $description;
            }
        }

        return 'Permission for ' . str_replace('_', ' ', $permissionName);
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->can('view_roles');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('create_roles');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->can('update_roles');
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->can('delete_roles');
    }
}
