<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AbuseReportResource\Pages;
use App\Models\AbuseReport;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class AbuseReportResource extends Resource
{
    protected static ?string $model = AbuseReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-exclamation';

    protected static ?string $navigationLabel = 'Abuse Reports';

    protected static ?string $modelLabel = 'Abuse Report';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationGroup = 'Safeguarding';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Report Information')
                    ->schema([
                        Forms\Components\TextInput::make('report_id')
                            ->label('Report ID')
                            ->disabled()
                            ->required(),
                        
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'in_progress' => 'In Progress',
                                'resolved' => 'Resolved',
                                'closed' => 'Closed',
                            ])
                            ->required()
                            ->default('pending'),
                        
                        Forms\Components\Select::make('assigned_to')
                            ->label('Assigned To')
                            ->options(User::all()->pluck('name', 'id'))
                            ->searchable()
                            ->nullable(),
                        
                        Forms\Components\DateTimePicker::make('resolved_at')
                            ->label('Resolved At')
                            ->nullable(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Incident Details')
                    ->schema([
                        Forms\Components\Select::make('incident_type')
                            ->label('Incident Type')
                            ->options([
                                'physical-abuse' => 'Physical Abuse',
                                'sexual-harassment' => 'Sexual Harassment',
                                'sexual-assault' => 'Sexual Assault',
                                'verbal-abuse' => 'Verbal Abuse',
                                'bullying' => 'Bullying',
                                'discrimination' => 'Discrimination',
                                'stalking' => 'Stalking',
                                'emotional-abuse' => 'Emotional/Psychological Abuse',
                                'financial-exploitation' => 'Financial Exploitation',
                                'neglect' => 'Neglect',
                                'other' => 'Other',
                            ])
                            ->required()
                            ->disabled(),
                        
                        Forms\Components\DatePicker::make('incident_date')
                            ->label('Incident Date')
                            ->required()
                            ->disabled(),
                        
                        Forms\Components\Textarea::make('incident_location')
                            ->label('Incident Location')
                            ->required()
                            ->disabled()
                            ->rows(2),
                        
                        Forms\Components\Textarea::make('persons_involved')
                            ->label('Persons Involved')
                            ->required()
                            ->disabled()
                            ->rows(3),
                        
                        Forms\Components\Textarea::make('detailed_description')
                            ->label('Detailed Description')
                            ->required()
                            ->disabled()
                            ->rows(5),
                    ]),

                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Textarea::make('witnesses_present')
                            ->label('Witnesses Present')
                            ->disabled()
                            ->rows(3),
                        
                        Forms\Components\Textarea::make('previously_reported')
                            ->label('Previously Reported')
                            ->disabled()
                            ->rows(3),
                        
                        Forms\Components\Textarea::make('evidence_available')
                            ->label('Evidence Available')
                            ->disabled()
                            ->rows(3),
                    ])
                    ->collapsible(),

                Forms\Components\Section::make('Reporter Information')
                    ->schema([
                        Forms\Components\Toggle::make('anonymous_report')
                            ->label('Anonymous Report')
                            ->disabled(),
                        
                        Forms\Components\TextInput::make('reporter_name')
                            ->label('Reporter Name')
                            ->disabled(),
                        
                        Forms\Components\TextInput::make('reporter_email')
                            ->label('Reporter Email')
                            ->email()
                            ->disabled(),
                        
                        Forms\Components\TextInput::make('reporter_phone')
                            ->label('Reporter Phone')
                            ->disabled(),
                        
                        Forms\Components\TextInput::make('reporter_relationship')
                            ->label('Relationship to Incident')
                            ->disabled(),
                        
                        Forms\Components\TextInput::make('preferred_contact')
                            ->label('Preferred Contact Method')
                            ->disabled(),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('report_id')
                    ->label('Report ID')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),
                
                Tables\Columns\BadgeColumn::make('incident_type')
                    ->label('Type')
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'physical-abuse' => 'Physical Abuse',
                        'sexual-harassment' => 'Sexual Harassment',
                        'sexual-assault' => 'Sexual Assault',
                        'verbal-abuse' => 'Verbal Abuse',
                        'bullying' => 'Bullying',
                        'discrimination' => 'Discrimination',
                        'stalking' => 'Stalking',
                        'emotional-abuse' => 'Emotional Abuse',
                        'financial-exploitation' => 'Financial Exploitation',
                        'neglect' => 'Neglect',
                        'other' => 'Other',
                        default => ucfirst($state),
                    })
                    ->colors([
                        'danger' => ['physical-abuse', 'sexual-assault', 'sexual-harassment'],
                        'warning' => ['bullying', 'stalking', 'verbal-abuse'],
                        'info' => ['discrimination', 'emotional-abuse'],
                        'gray' => ['other'],
                    ])
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('incident_date')
                    ->label('Incident Date')
                    ->date()
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('anonymous_report')
                    ->label('Anonymous')
                    ->boolean()
                    ->trueIcon('heroicon-o-eye-slash')
                    ->falseIcon('heroicon-o-user')
                    ->trueColor('warning')
                    ->falseColor('success'),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'in_progress',
                        'success' => 'resolved',
                        'gray' => 'closed',
                    ])
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('assignedUser.name')
                    ->label('Assigned To')
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('incident_type')
                    ->label('Incident Type')
                    ->options([
                        'physical-abuse' => 'Physical Abuse',
                        'sexual-harassment' => 'Sexual Harassment',
                        'sexual-assault' => 'Sexual Assault',
                        'verbal-abuse' => 'Verbal Abuse',
                        'bullying' => 'Bullying',
                        'discrimination' => 'Discrimination',
                        'stalking' => 'Stalking',
                        'emotional-abuse' => 'Emotional/Psychological Abuse',
                        'financial-exploitation' => 'Financial Exploitation',
                        'neglect' => 'Neglect',
                        'other' => 'Other',
                    ]),
                
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'in_progress' => 'In Progress',
                        'resolved' => 'Resolved',
                        'closed' => 'Closed',
                    ]),
                
                Tables\Filters\TernaryFilter::make('anonymous_report')
                    ->label('Anonymous Reports')
                    ->placeholder('All reports')
                    ->trueLabel('Anonymous only')
                    ->falseLabel('Identified only'),
                
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Submitted From'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Submitted Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
                
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
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
            'index' => Pages\ListAbuseReports::route('/'),
            'view' => Pages\ViewAbuseReport::route('/{record}'),
            'edit' => Pages\EditAbuseReport::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
}
