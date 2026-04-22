<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JobListingResource\Pages;
use App\Filament\Resources\JobListingResource\RelationManagers;
use App\Models\JobListing;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class JobListingResource extends Resource
{
    protected static ?string $model = JobListing::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = 'Content Management';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Job Details')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', \Illuminate\Support\Str::slug($state)))
                            ->placeholder('e.g., Senior Lecturer - Computer Science'),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\RichEditor::make('description')
                            ->required()
                            ->columnSpanFull()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'link',
                                'undo',
                                'redo',
                            ])
                            ->placeholder('Provide a detailed description of the position'),
                        Forms\Components\RichEditor::make('requirements')
                            ->columnSpanFull()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'bulletList',
                                'orderedList',
                                'undo',
                                'redo',
                            ])
                            ->placeholder('List the required qualifications and skills'),
                        Forms\Components\RichEditor::make('responsibilities')
                            ->columnSpanFull()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'bulletList',
                                'orderedList',
                                'undo',
                                'redo',
                            ])
                            ->placeholder('Describe the key responsibilities'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Job Information')
                    ->schema([
                        Forms\Components\TextInput::make('location')
                            ->placeholder('e.g., Kampala, Uganda'),
                        Forms\Components\TextInput::make('department')
                            ->placeholder('e.g., Computer Science Department'),
                        Forms\Components\TextInput::make('reports_to')
                            ->placeholder('e.g., Head of Department'),
                        Forms\Components\Textarea::make('supervises_who')
                            ->placeholder('e.g., Teaching Assistants, Research Associates')
                            ->rows(2),
                        Forms\Components\Select::make('employment_type')
                            ->options([
                                'full-time' => 'Full-time',
                                'part-time' => 'Part-time',
                                'contract' => 'Contract',
                                'temporary' => 'Temporary',
                            ])
                            ->placeholder('Select employment type'),
                        Forms\Components\TextInput::make('salary_range')
                            ->placeholder('e.g., UGX 3,000,000 - 5,000,000'),
                        Forms\Components\DatePicker::make('application_deadline')
                            ->native(false)
                            ->minDate(now()),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Job Description Document')
                    ->description('Upload a detailed job description document (PDF or Word)')
                    ->schema([
                        Forms\Components\FileUpload::make('document_path')
                            ->label('Job Description Document')
                            ->disk('public')
                            ->directory('job-documents')
                            ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                            ->maxSize(10240) // 10MB
                            ->maxFiles(1) // Only allow single file
                            ->downloadable()
                            ->openable()
                            ->previewable(false)
                            ->helperText('Upload PDF or Word document (max 10MB)')
                            ->visibility('public')
                            ->dehydrateStateUsing(fn ($state) => is_array($state) ? ($state[0] ?? null) : $state)
                            ->afterStateHydrated(fn ($component, $state) => $component->state($state ? [$state] : [])),
                        Forms\Components\Hidden::make('document_name'),
                        Forms\Components\Hidden::make('document_size'),
                        Forms\Components\Hidden::make('document_type'),
                    ])
                    ->collapsible(),
                
                Forms\Components\Section::make('Publishing')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->required()
                            ->options([
                                'draft' => 'Draft',
                                'active' => 'Active',
                                'closed' => 'Closed',
                                'archived' => 'Archived',
                            ])
                            ->default('draft')
                            ->live(),
                        Forms\Components\DateTimePicker::make('published_at')
                            ->native(false)
                            ->visible(fn ($get) => $get('status') === 'active'),
                        Forms\Components\Hidden::make('created_by')
                            ->default(auth()->id()),
                        Forms\Components\Hidden::make('updated_by')
                            ->default(auth()->id()),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('department')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('location')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('employment_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'full-time' => 'success',
                        'part-time' => 'warning',
                        'contract' => 'info',
                        'temporary' => 'gray',
                        default => 'gray',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('reports_to')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('salary_range')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('has_document')
                    ->label('Document')
                    ->boolean()
                    ->getStateUsing(fn ($record) => !empty($record->document_path))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('application_deadline')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'active' => 'success',
                        'closed' => 'warning',
                        'archived' => 'danger',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Created By')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('download_document')
                    ->label('Download')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('info')
                    ->visible(fn ($record) => !empty($record->document_path))
                    ->url(fn ($record) => route('job-listing.download-document', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
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
            'index' => Pages\ListJobListings::route('/'),
            'create' => Pages\CreateJobListing::route('/create'),
            'edit' => Pages\EditJobListing::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->can('view_job_listings');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('create_job_listings');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->can('update_job_listings');
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->can('delete_job_listings');
    }
}
