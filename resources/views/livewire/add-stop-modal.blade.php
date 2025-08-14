<div>
<div x-data="{ open: @entangle('show') }">
    <div x-show="open" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg w-96">
            <h3 class="text-lg font-bold mb-4">إضافة نقطة توقف</h3>

            <div class="mb-2">
                <label class="block">الاسم</label>
                <input type="text" wire:model.defer="name" class="border rounded w-full p-1">
                @error('name') <span class="text-red-500">{{ $message }}</span> @enderror
            </div>

            <div class="mb-2">
                <label class="block">العنوان</label>
                <input type="text" wire:model.defer="address" class="border rounded w-full p-1">
                @error('address') <span class="text-red-500">{{ $message }}</span> @enderror
            </div>

            <div class="mb-2">
                <label class="block">الوصف</label>
                <input type="text" wire:model.defer="description" class="border rounded w-full p-1">
                @error('description') <span class="text-red-500">{{ $message }}</span> @enderror
            </div>

            <div class="flex justify-end space-x-2 mt-4">
                <button type="button" @click="open=false" class="px-3 py-1 bg-gray-300 rounded">إلغاء</button>
                <button type="button" wire:click="save" class="px-3 py-1 bg-blue-500 text-white rounded">حفظ</button>
            </div>
        </div>
    </div>
</div>

</div>
