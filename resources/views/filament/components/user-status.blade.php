@auth
<div class="flex items-center space-x-3 rtl:space-x-reverse text-sm">
    <div class="flex items-center">
        <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center ml-2 rtl:ml-0 rtl:mr-2">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
        </div>
        <div>
            <div class="font-medium text-gray-900 dark:text-white">
                {{ auth()->user()->name_ar ?? auth()->user()->name }}
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400">
                {{ auth()->user()->email }}
            </div>
        </div>
    </div>
</div>
@endauth