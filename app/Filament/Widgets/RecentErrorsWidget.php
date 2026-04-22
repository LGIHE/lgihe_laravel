<?php

namespace App\Filament\Widgets;

use App\Models\AnalyticsError;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentErrorsWidget extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Recent Errors (Last 24 Hours)')
            ->query(
                AnalyticsError::query()
                    ->where('timestamp', '>=', now()->subDay())
                    ->orderByDesc('timestamp')
                    ->limit(20)
            )
            ->columns([
                Tables\Columns\TextColumn::make('severity')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'low' => 'gray',
                        'medium' => 'warning',
                        'high' => 'danger',
                        'critical' => 'danger',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('message')
                    ->label('Error Message')
                    ->limit(80)
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('url')
                    ->label('Page URL')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('timestamp')
                    ->label('Time')
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->defaultSort('timestamp', 'desc')
            ->paginated(false)
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Details')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Error Details')
                    ->modalContent(fn (AnalyticsError $record): \Illuminate\Contracts\View\View => view(
                        'filament.widgets.error-details',
                        ['record' => $record]
                    ))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
            ]);
    }
    
    public function getTableRecordKey($record): string
    {
        return (string) ($record->id ?? uniqid());
    }
}
