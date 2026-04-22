<div class="space-y-4">
    <div>
        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Error Message</h3>
        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $record->message }}</p>
    </div>

    @if($record->url)
    <div>
        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">URL</h3>
        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100 break-all">{{ $record->url }}</p>
    </div>
    @endif

    @if($record->stack)
    <div>
        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Stack Trace</h3>
        <pre class="mt-1 text-xs text-gray-900 dark:text-gray-100 bg-gray-100 dark:bg-gray-800 p-4 rounded overflow-x-auto">{{ $record->stack }}</pre>
    </div>
    @endif

    @if($record->user_agent)
    <div>
        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">User Agent</h3>
        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100 break-all">{{ $record->user_agent }}</p>
    </div>
    @endif

    <div>
        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Severity</h3>
        <p class="mt-1">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                @if($record->severity === 'critical') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                @elseif($record->severity === 'high') bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200
                @elseif($record->severity === 'medium') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200
                @endif">
                {{ ucfirst($record->severity) }}
            </span>
        </p>
    </div>

    <div>
        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Timestamp</h3>
        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $record->timestamp->format('F j, Y g:i A') }}</p>
    </div>
</div>
