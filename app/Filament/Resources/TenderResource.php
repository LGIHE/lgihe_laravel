<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TenderResource\Pages;
use App\Models\Tender;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TenderResource extends Resource
{
    protected static ?string $model = Tender::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Content Management';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Tender Details')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', \Illuminate\Support\Str::slug($state)))
                            ->placeholder('e.g., Supply of Laboratory Equipment'),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('reference_number')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->placeholder('e.g., TENDER/2026/001'),
                        Forms\Components\Select::make('category')
                            ->options([
                                'goods' => 'Goods',
                                'services' => 'Services',
                                'works' => 'Works',
                                'consultancy' => 'Consultancy',
                            ])
                            ->placeholder('Select category'),
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
                            ->placeholder('Provide a detailed description of the tender'),
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
                            ->placeholder('List the requirements for bidders'),
                        Forms\Components\DatePicker::make('closing_date')
                            ->required()
                            ->native(false)
                            ->minDate(now()),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('RFP Document')
                    ->description('Upload the Request for Proposal (RFP) document')
                    ->schema([
                        Forms\Components\FileUpload::make('rfp_document_path')
                            ->label('RFP Document')
                            ->disk('public')
                            ->directory('tender-documents/rfp')
                            ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                            ->maxSize(10240) // 10MB
                            ->downloadable()
                            ->openable()
                            ->previewable(false)
                            ->helperText('Upload PDF or Word document (max 10MB)')
                            ->visibility('public')
                            ->getUploadedFileNameForStorageUsing(function ($file, $get) {
                                $slug = $get('slug') ?: 'tender';
                                // Get original extension
                                $extension = $file->getClientOriginalExtension();
                                
                                return "{$slug}_RFP.{$extension}";
                            }),
                        Forms\Components\Hidden::make('rfp_document_name'),
                        Forms\Components\Hidden::make('rfp_document_size'),
                        Forms\Components\Hidden::make('rfp_document_type'),
                    ])
                    ->collapsible(),

                Forms\Components\Section::make('ToR Document')
                    ->description('Upload the Terms of Reference (ToR) document')
                    ->schema([
                        Forms\Components\FileUpload::make('tor_document_path')
                            ->label('ToR Document')
                            ->disk('public')
                            ->directory('tender-documents/tor')
                            ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                            ->maxSize(10240) // 10MB
                            ->downloadable()
                            ->openable()
                            ->previewable(false)
                            ->helperText('Upload PDF or Word document (max 10MB)')
                            ->visibility('public')
                            ->getUploadedFileNameForStorageUsing(function ($file, $get) {
                                $slug = $get('slug') ?: 'tender';
                                // Get original extension
                                $extension = $file->getClientOriginalExtension();
                                
                                return "{$slug}_ToR.{$extension}";
                            }),
                        Forms\Components\Hidden::make('tor_document_name'),
                        Forms\Components\Hidden::make('tor_document_size'),
                        Forms\Components\Hidden::make('tor_document_type'),
                    ])
                    ->collapsible(),
                
                Forms\Components\Section::make('Publishing')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->required()
                            ->options([
                                'draft' => 'Draft',
                                'open' => 'Open',
                                'closed' => 'Closed',
                                'awarded' => 'Awarded',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('draft')
                            ->live(),
                        Forms\Components\DateTimePicker::make('published_at')
                            ->native(false)
                            ->visible(fn ($get) => $get('status') === 'open'),
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
                Tables\Columns\TextColumn::make('reference_number')
                    ->label('Ref. Number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'goods' => 'success',
                        'services' => 'info',
                        'works' => 'warning',
                        'consultancy' => 'primary',
                        default => 'gray',
                    })
                    ->searchable(),
                Tables\Columns\IconColumn::make('has_rfp')
                    ->label('RFP')
                    ->boolean()
                    ->getStateUsing(fn ($record) => !empty($record->rfp_document_path))
                    ->toggleable(),
                Tables\Columns\IconColumn::make('has_tor')
                    ->label('ToR')
                    ->boolean()
                    ->getStateUsing(fn ($record) => !empty($record->tor_document_path))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('closing_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'open' => 'success',
                        'closed' => 'warning',
                        'awarded' => 'info',
                        'cancelled' => 'danger',
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
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'open' => 'Open',
                        'closed' => 'Closed',
                        'awarded' => 'Awarded',
                        'cancelled' => 'Cancelled',
                    ]),
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'goods' => 'Goods',
                        'services' => 'Services',
                        'works' => 'Works',
                        'consultancy' => 'Consultancy',
                    ]),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('download_rfp')
                        ->label('Download RFP')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('info')
                        ->visible(fn ($record) => !empty($record->rfp_document_path))
                        ->url(fn ($record) => route('tender.download-rfp', $record))
                        ->openUrlInNewTab(),
                    Tables\Actions\Action::make('download_tor')
                        ->label('Download ToR')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('success')
                        ->visible(fn ($record) => !empty($record->tor_document_path))
                        ->url(fn ($record) => route('tender.download-tor', $record))
                        ->openUrlInNewTab(),
                    Tables\Actions\EditAction::make(),
                ]),
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
            'index' => Pages\ListTenders::route('/'),
            'create' => Pages\CreateTender::route('/create'),
            'edit' => Pages\EditTender::route('/{record}/edit'),
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
        return auth()->user()->can('view_tenders');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('create_tenders');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->can('update_tenders');
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->can('delete_tenders');
    }
}
