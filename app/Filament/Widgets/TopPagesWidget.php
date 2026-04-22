<?php

namespace App\Filament\Widgets;

use App\Models\AnalyticsEvent;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class TopPagesWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        // Get database driver
        $driver = DB::connection()->getDriverName();
        
        // Build query based on database driver
        if ($driver === 'sqlite') {
            $query = AnalyticsEvent::query()
                ->where('name', 'page_view')
                ->where('timestamp', '>=', now()->subDays(30))
                ->select(
                    DB::raw("json_extract(properties, '$.page') as page"),
                    DB::raw('count(*) as views'),
                    DB::raw('count(DISTINCT session_id) as unique_visitors')
                )
                ->groupBy('page')
                ->orderByDesc('views')
                ->limit(10);
        } else {
            // MySQL/PostgreSQL
            $query = AnalyticsEvent::query()
                ->where('name', 'page_view')
                ->where('timestamp', '>=', now()->subDays(30))
                ->select(
                    DB::raw('JSON_UNQUOTE(JSON_EXTRACT(properties, "$.page")) as page'),
                    DB::raw('count(*) as views'),
                    DB::raw('count(DISTINCT session_id) as unique_visitors')
                )
                ->groupBy('page')
                ->orderByDesc('views')
                ->limit(10);
        }
        
        return $table
            ->heading('Most Viewed Pages (Last 30 Days)')
            ->query($query)
            ->columns([
                Tables\Columns\TextColumn::make('page')
                    ->label('Page')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ? trim($state, '"') : 'Unknown'),
                Tables\Columns\TextColumn::make('views')
                    ->label('Total Views')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unique_visitors')
                    ->label('Unique Visitors')
                    ->numeric()
                    ->sortable(),
            ])
            ->defaultSort('views', 'desc')
            ->paginated(false);
    }
    
    public function getTableRecordKey($record): string
    {
        // Use page as unique key, or generate one
        $page = $record->page ?? 'unknown';
        return md5($page . '_' . ($record->views ?? 0));
    }
}
