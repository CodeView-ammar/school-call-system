<div class="space-y-4">
    <!-- البحث عن الطلاب -->
    <div class="relative">
        <label for="student_search" class="block text-sm font-medium text-gray-700 mb-2">
            البحث عن الطلاب
        </label>
        <input 
            type="text" 
            id="student_search"
            wire:model.live="search" 
            placeholder="ابحث باسم الطالب أو كود الطالب..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
        >
        
        <!-- نتائج البحث -->
        @if($showResults && count($searchResults) > 0)
            <div class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto">
                @foreach($searchResults as $student)
                    <div 
                        wire:click="selectStudent({{ $student['id'] }})"
                        class="px-4 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-200 last:border-b-0"
                    >
                        <div class="font-medium text-gray-900">{{ $student['name_ar'] }}</div>
                        <div class="text-sm text-gray-500">كود: {{ $student['code'] }} | رقم: {{ $student['student_number'] }}</div>
                    </div>
                @endforeach
            </div>
        @elseif($showResults && strlen($search) >= 2)
            <div class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg">
                <div class="px-4 py-2 text-gray-500 text-center">
                    لا توجد نتائج للبحث "{{ $search }}"
                </div>
            </div>
        @endif
    </div>

    <!-- الطلاب المحددين -->
    @if(count($selectedStudents) > 0)
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                الطلاب المحددين ({{ count($selectedStudents) }})
            </label>
            <div class="space-y-2">
                @foreach($selectedStudents as $index => $student)
                    <div class="flex items-center justify-between p-3 bg-green-50 border border-green-200 rounded-md">
                        <div class="flex-1">
                            <div class="font-medium text-green-900">{{ $student['name_ar'] }}</div>
                            <div class="text-sm text-green-700">كود: {{ $student['code'] }}</div>
                        </div>
                        <button 
                            type="button"
                            wire:click="removeStudent({{ $index }})"
                            class="text-red-600 hover:text-red-800 font-medium"
                        >
                            إزالة
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- رسالة توضيحية -->
    <div class="text-sm text-gray-600 bg-blue-50 p-3 rounded-md">
        <p class="font-medium mb-1">كيفية الاستخدام:</p>
        <ul class="list-disc list-inside space-y-1">
            <li>ابدأ بكتابة اسم الطالب أو كود الطالب</li>
            <li>اختر الطالب من النتائج المعروضة</li>
            <li>يمكنك إضافة عدة طلاب</li>
            <li>يمكنك إزالة أي طالب بالضغط على "إزالة"</li>
        </ul>
    </div>
</div>