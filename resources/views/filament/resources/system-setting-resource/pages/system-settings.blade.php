
<x-filament-panels::page>
    <div class="space-y-6">
        <!-- عرض معلومات المدرسة -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    {{ $this->getTitle() }}
                </h3>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    آخر تحديث: {{ now()->format('Y-m-d H:i') }}
                </div>
            </div>
            
            <!-- نموذج الإعدادات -->
            <form wire:submit="save">
                {{ $this->form }}
                
                <div class="mt-6 flex justify-end">
                    <x-filament::button 
                        type="submit"
                        color="primary"
                        size="lg"
                        :loading="$this->saving ?? false"
                        wire:target="save"
                    >
                        <x-heroicon-m-check class="w-5 h-5 mr-2"/>
                        حفظ جميع الإعدادات
                    </x-filament::button>
                </div>
            </form>
        </div>
    </div>
</x-filament-panels::page>
