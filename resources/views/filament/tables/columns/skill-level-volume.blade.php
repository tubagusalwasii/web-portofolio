@php
    $level = $getState();
    $percentage = match ($level) {
        'Dasar' => 33,
        'Menengah' => 66,
        'Ahli' => 100,
        default => 0,
    };
    $color = match ($level) {
        'Dasar' => 'bg-danger-500',
        'Menengah' => 'bg-warning-500',
        'Ahli' => 'bg-success-500',
        default => 'bg-gray-500',
    };
@endphp

<div class="px-4 py-3">
    <div class="flex items-center space-x-2 w-full max-w-[12rem]">
        <span class="text-sm font-medium text-gray-700 dark:text-gray-200 min-w-[4.5rem]">
            {{ $level }}
        </span>
        <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden dark:bg-gray-700">
            <div class="h-full {{ $color }} rounded-full" style="width: {{ $percentage }}%"></div>
        </div>
    </div>
</div>
