<?php

namespace App\Filament\Widgets;

use App\Models\AnalyticsEvent;
use App\Models\AnalyticsError;
use App\Models\PageLoad;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class AnalyticsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $today = now()->startOfDay();
        $last30Days = now()->subDays(30);

        // Total page views (last 30 days)
        $pageViews = AnalyticsEvent::where('name', 'page_view')
            ->where('timestamp', '>=', $last30Days)
            ->count();

        $pageViewsToday = AnalyticsEvent::where('name', 'page_view')
            ->where('timestamp', '>=', $today)
            ->count();

        // Unique visitors (last 30 days) - using DB::raw for better SQLite compatibility
        $uniqueVisitors = AnalyticsEvent::where('timestamp', '>=', $last30Days)
            ->whereNotNull('session_id')
            ->select(DB::raw('COUNT(DISTINCT session_id) as count'))
            ->value('count') ?? 0;

        $uniqueVisitorsToday = AnalyticsEvent::where('timestamp', '>=', $today)
            ->whereNotNull('session_id')
            ->select(DB::raw('COUNT(DISTINCT session_id) as count'))
            ->value('count') ?? 0;

        // Average load time (last 30 days)
        $avgLoadTime = PageLoad::where('timestamp', '>=', $last30Days)
            ->avg('load_time');

        $avgLoadTimeToday = PageLoad::where('timestamp', '>=', $today)
            ->avg('load_time');

        // Error count (last 30 days)
        $errors = AnalyticsError::where('timestamp', '>=', $last30Days)->count();
        $errorsToday = AnalyticsError::where('timestamp', '>=', $today)->count();

        // Active visitors (last 5 minutes)
        $activeVisitors = AnalyticsEvent::where('timestamp', '>=', now()->subMinutes(5))
            ->whereNotNull('session_id')
            ->select(DB::raw('COUNT(DISTINCT session_id) as count'))
            ->value('count') ?? 0;

        return [
            Stat::make('Page Views (30d)', number_format($pageViews))
                ->description($pageViewsToday . ' today')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart($this->getPageViewsChart()),

            Stat::make('Unique Visitors (30d)', number_format($uniqueVisitors))
                ->description($uniqueVisitorsToday . ' today')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),

            Stat::make('Avg Load Time (30d)', round($avgLoadTime ?? 0) . 'ms')
                ->description(round($avgLoadTimeToday ?? 0) . 'ms today')
                ->descriptionIcon($avgLoadTime > 2000 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($avgLoadTime > 2000 ? 'danger' : 'success'),

            Stat::make('Errors (30d)', number_format($errors))
                ->description($errorsToday . ' today')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($errors > 100 ? 'danger' : 'warning'),

            Stat::make('Active Visitors', number_format($activeVisitors))
                ->description('Last 5 minutes')
                ->descriptionIcon('heroicon-m-signal')
                ->color('info'),
        ];
    }

    protected function getPageViewsChart(): array
    {
        $driver = DB::connection()->getDriverName();
        
        if ($driver === 'sqlite') {
            // SQLite uses date() function
            $data = AnalyticsEvent::where('name', 'page_view')
                ->where('timestamp', '>=', now()->subDays(7))
                ->select(DB::raw("date(timestamp) as date"), DB::raw('count(*) as count'))
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('count')
                ->toArray();
        } else {
            // MySQL/PostgreSQL
            $data = AnalyticsEvent::where('name', 'page_view')
                ->where('timestamp', '>=', now()->subDays(7))
                ->select(DB::raw('DATE(timestamp) as date'), DB::raw('count(*) as count'))
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('count')
                ->toArray();
        }

        return array_pad($data, 7, 0);
    }
}
