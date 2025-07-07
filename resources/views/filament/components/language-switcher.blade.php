@php
    $currentLocale = app()->getLocale();
    $locales = [
        'en' => 'English',
        'ar' => 'العربية'
    ];
@endphp

<div class="fi-dropdown fi-dropdown-panel">
    <x-filament::dropdown>
        <x-slot name="trigger">
            <button 
                type="button"
                class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus:ring-2 rounded-lg fi-color-gray fi-btn-color-gray fi-size-sm fi-btn-size-sm gap-1 px-3 py-2 text-sm inline-grid shadow-sm bg-white text-gray-950 hover:bg-gray-50 focus:ring-primary-600 dark:bg-gray-900 dark:text-white dark:hover:bg-gray-800 ring-1 ring-gray-950/10 dark:ring-white/20"
            >
                <x-heroicon-m-language class="fi-btn-icon h-4 w-4" />
                <span class="fi-btn-label">{{ $locales[$currentLocale] }}</span>
                <x-heroicon-s-chevron-down class="fi-dropdown-trigger-icon h-4 w-4" />
            </button>
        </x-slot>
        <x-filament::dropdown.list>
            @foreach($locales as $code => $name)
                <div class="flex items-center {{ $currentLocale === $code ? 'bg-gray-50 dark:bg-gray-800' : '' }}">
                    @if($code === $currentLocale)
                        <svg class="fi-dropdown-list-item-icon h-5 w-5 text-gray-400 dark:text-gray-500 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"></path>
                        </svg>
                    @endif
                    <a href="{{ route('filament.admin.locale', $code) }}">
                        {{ $name }}
                    </a>
                </div>
            @endforeach
        </x-filament::dropdown.list>
    </x-filament::dropdown>
</div>