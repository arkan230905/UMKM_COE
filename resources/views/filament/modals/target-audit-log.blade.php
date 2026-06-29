<div class="space-y-4">
    @forelse($logs as $log)
    <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 bg-white dark:bg-gray-900">
        <div class="flex items-start justify-between">
            <div class="flex items-start gap-3">
                <div class="rounded-full p-2 {{ $log->action === 'created' ? 'bg-success-100 dark:bg-success-400/10' : ($log->action === 'updated' ? 'bg-warning-100 dark:bg-warning-400/10' : 'bg-danger-100 dark:bg-danger-400/10') }}">
                    @if($log->action === 'created')
                        <svg class="h-5 w-5 text-success-600 dark:text-success-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    @elseif($log->action === 'updated')
                        <svg class="h-5 w-5 text-warning-600 dark:text-warning-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    @else
                        <svg class="h-5 w-5 text-danger-600 dark:text-danger-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    @endif
                </div>
                
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $log->action === 'created' ? 'bg-success-100 text-success-800 dark:bg-success-400/10 dark:text-success-400' : ($log->action === 'updated' ? 'bg-warning-100 text-warning-800 dark:bg-warning-400/10 dark:text-warning-400' : 'bg-danger-100 text-danger-800 dark:bg-danger-400/10 dark:text-danger-400') }}">
                            {{ $log->action_label }}
                        </span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">oleh</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $log->user->name ?? 'Unknown' }}</span>
                    </div>
                    
                    <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">
                        {{ $log->description }}
                    </p>
                    
                    @if($log->old_data || $log->new_data)
                    <div class="mt-3 space-y-2">
                        @if($log->old_data && $log->action === 'updated')
                        <details class="text-xs">
                            <summary class="cursor-pointer text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                                Lihat data sebelumnya
                            </summary>
                            <pre class="mt-2 p-2 bg-gray-50 dark:bg-gray-800 rounded text-xs overflow-x-auto">{{ json_encode($log->old_data, JSON_PRETTY_PRINT) }}</pre>
                        </details>
                        @endif
                        
                        @if($log->new_data && in_array($log->action, ['created', 'updated']))
                        <details class="text-xs">
                            <summary class="cursor-pointer text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                                Lihat data {{ $log->action === 'created' ? 'baru' : 'sesudah perubahan' }}
                            </summary>
                            <pre class="mt-2 p-2 bg-gray-50 dark:bg-gray-800 rounded text-xs overflow-x-auto">{{ json_encode($log->new_data, JSON_PRETTY_PRINT) }}</pre>
                        </details>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
            
            <div class="text-right">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ $log->created_at->format('d M Y') }}
                </p>
                <p class="text-xs text-gray-400 dark:text-gray-500">
                    {{ $log->created_at->format('H:i') }}
                </p>
            </div>
        </div>
    </div>
    @empty
    <div class="text-center py-8">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Belum ada riwayat perubahan</p>
    </div>
    @endforelse
</div>
