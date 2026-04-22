<?php

namespace App\Filament\Widgets;

use App\Models\AnalyticsEvent;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class TrafficByCountryWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Traffic by Country (Last 30 Days)')
            ->query(
                AnalyticsEvent::query()
                    ->where('timestamp', '>=', now()->subDays(30))
                    ->whereNotNull('country')
                    ->select(
                        'country',
                        'country_code',
                        DB::raw('COUNT(*) as visits'),
                        DB::raw('COUNT(DISTINCT session_id) as unique_visitors')
                    )
                    ->groupBy('country', 'country_code')
                    ->orderByDesc('visits')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('country_code')
                    ->label('Code')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('country')
                    ->label('Country')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('visits')
                    ->label('Total Visits')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unique_visitors')
                    ->label('Unique Visitors')
                    ->numeric()
                    ->sortable(),
            ])
            ->defaultSort('visits', 'desc')
            ->paginated(false);
    }
    
    public function getTableRecordKey($record): string
    {
        // Use country_code as unique key
        return $record->country_code ?? md5($record->country ?? uniqid());
    }
}
