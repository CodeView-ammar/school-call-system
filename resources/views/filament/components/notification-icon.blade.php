<a href="{{ route('notifications') }}" class="relative flex items-center justify-center w-10 h-10">
    <x-heroicon-o-bell class="w-6 h-6 text-gray-600 dark:text-gray-300" />
    @if($unreadCount = Illuminate\Notifications\DatabaseNotification::where('is_read', false)->count())
        <span class="absolute top-1.5 right-1.5 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-red-100 bg-red-600 rounded-full">
            {{ $unreadCount }}
        </span>
    @endif
</a>
