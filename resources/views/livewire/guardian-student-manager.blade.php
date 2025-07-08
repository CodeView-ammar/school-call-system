<div class="space-y-6">
    <!-- رسائل التنبيه -->
    @if (session()->has('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    @if (session()->has('warning'))
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('warning') }}</span>
        </div>
    @endif

    <!-- البحث عن طلاب جدد -->
    <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-medium text-gray-900 mb-4">إضافة طلاب جدد</h3>
        
        <div class="relative">
            <input 
                type="text" 
                wire:model.live="searchTerm"
                placeholder="ابحث عن طالب باسمه أو كود الطالب..."
                class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
            >
            
            <!-- نتائج البحث -->
            @if($showSearchResults && count($searchResults) > 0)
                <div class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto">
                    @foreach($searchResults as $student)
                        <div class="px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900">{{ $student['name_ar'] }}</div>
                                    <div class="text-sm text-gray-500">
                                        كود: {{ $student['code'] }} 
                                        @if($student['student_number'])
                                            | رقم: {{ $student['student_number'] }}
                                        @endif
                                    </div>
                                </div>
                                <div class="flex space-x-2">
                                    <button 
                                        wire:click="addStudent({{ $student['id'] }}, true)"
                                        class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700"
                                    >
                                        إضافة كرئيسي
                                    </button>
                                    <button 
                                        wire:click="addStudent({{ $student['id'] }}, false)"
                                        class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700"
                                    >
                                        إضافة كثانوي
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @elseif($showSearchResults && strlen($searchTerm) >= 2)
                <div class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg">
                    <div class="px-4 py-3 text-gray-500 text-center">
                        لا توجد نتائج للبحث "{{ $searchTerm }}"
                    </div>
                </div>
            @endif
        </div>
        
        <p class="mt-2 text-sm text-gray-600">
            اكتب على الأقل حرفين للبحث عن الطلاب
        </p>
    </div>

    <!-- قائمة الطلاب المرتبطين -->
    <div class="bg-white p-6 rounded-lg shadow">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">
                الطلاب المرتبطين ({{ count($selectedStudents) }})
            </h3>
            @if(count($selectedStudents) > 0)
                <button 
                    wire:click="saveChanges"
                    class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                    حفظ التغييرات
                </button>
            @endif
        </div>

        @if(count($selectedStudents) > 0)
            <div class="space-y-3">
                @foreach($selectedStudents as $index => $student)
                    <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg {{ isset($student['is_new']) && $student['is_new'] ? 'bg-green-50 border-green-200' : 'bg-gray-50' }}">
                        <div class="flex-1">
                            <div class="font-medium text-gray-900">
                                {{ $student['name_ar'] }}
                                @if(isset($student['is_new']) && $student['is_new'])
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mr-2">
                                        جديد
                                    </span>
                                @endif
                            </div>
                            <div class="text-sm text-gray-500">
                                كود: {{ $student['code'] }}
                                @if($student['student_number'])
                                    | رقم: {{ $student['student_number'] }}
                                @endif
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-3">
                            <!-- حالة ولي الأمر الرئيسي -->
                            <div class="flex items-center">
                                <button 
                                    wire:click="togglePrimaryStatus({{ $index }})"
                                    class="flex items-center space-x-2 px-3 py-1 rounded-full text-sm font-medium {{ $student['is_primary'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}"
                                >
                                    <span>{{ $student['is_primary'] ? 'رئيسي' : 'ثانوي' }}</span>
                                </button>
                            </div>
                            
                            <!-- زر الحذف -->
                            <button 
                                wire:click="removeStudent({{ $index }})"
                                class="text-red-600 hover:text-red-800 font-medium"
                            >
                                إزالة
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <div class="text-gray-500 mb-2">لا توجد طلاب مرتبطين بولي الأمر</div>
                <p class="text-sm text-gray-400">استخدم البحث أعلاه لإضافة طلاب</p>
            </div>
        @endif
    </div>

    <!-- معلومات إضافية -->
    <div class="bg-blue-50 p-4 rounded-lg">
        <h4 class="font-medium text-blue-900 mb-2">ملاحظات هامة:</h4>
        <ul class="text-sm text-blue-800 space-y-1">
            <li>• يمكن لولي الأمر الواحد أن يكون مرتبطاً بعدة طلاب</li>
            <li>• ولي الأمر "الرئيسي" هو الشخص المسؤول الأول عن الطالب</li>
            <li>• يمكن أن يكون للطالب الواحد عدة أولياء أمور (رئيسي وثانويين)</li>
            <li>• لا تنس الضغط على "حفظ التغييرات" بعد إجراء التعديلات</li>
        </ul>
    </div>
</div>