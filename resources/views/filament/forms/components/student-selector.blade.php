<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        x-data="studentSelector({
            state: $wire.entangle('{{ $getStatePath() }}'),
            searchUrl: '{{ route('api.students.search') }}',
        })"
        class="space-y-4"
    >
        <!-- البحث -->
        <div class="relative">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                {{ $getLabel() }}
            </label>
            
            <input
                x-model="search"
                @input.debounce.300ms="searchStudents()"
                type="text"
                placeholder="ابحث عن الطلاب..."
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-sm"
            >
            
            <!-- نتائج البحث -->
            <div
                x-show="showResults && searchResults.length > 0"
                x-transition
                class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto"
            >
                <template x-for="student in searchResults" :key="student.id">
                    <div
                        @click="selectStudent(student)"
                        class="px-4 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-200 last:border-b-0"
                    >
                        <div class="font-medium text-gray-900" x-text="student.name_ar"></div>
                        <div class="text-sm text-gray-500">
                            <span>كود: </span><span x-text="student.code"></span>
                            <span x-show="student.student_number"> | رقم: </span>
                            <span x-text="student.student_number"></span>
                            <span class="mr-2 px-2 py-0.5 bg-blue-100 text-blue-800 rounded-full text-xs" x-text="student.gender === 'male' ? 'ذكر' : 'أنثى'"></span>
                        </div>
                    </div>
                </template>
            </div>
            
            <!-- رسالة عدم وجود نتائج -->
            <div
                x-show="showResults && searchResults.length === 0 && search.length >= 2"
                x-transition
                class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg"
            >
                <div class="px-4 py-2 text-gray-500 text-center">
                    لا توجد نتائج للبحث "<span x-text="search"></span>"
                </div>
            </div>
        </div>

        <!-- الطلاب المحددين -->
        <div x-show="selectedStudents.length > 0" class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">
                الطلاب المحددين (<span x-text="selectedStudents.length"></span>)
            </label>
            
            <div class="space-y-2 max-h-40 overflow-y-auto">
                <template x-for="(student, index) in selectedStudents" :key="student.id">
                    <div class="flex items-center justify-between p-3 bg-green-50 border border-green-200 rounded-md">
                        <div class="flex-1">
                            <div class="font-medium text-green-900" x-text="student.name_ar"></div>
                            <div class="text-sm text-green-700">
                                <span>كود: </span><span x-text="student.code"></span>
                                <span x-show="student.student_number"> | رقم: </span>
                                <span x-text="student.student_number"></span>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs" x-text="student.gender === 'male' ? 'ذكر' : 'أنثى'"></span>
                            <button
                                @click="removeStudent(index)"
                                type="button"
                                class="text-red-600 hover:text-red-800 font-medium text-sm"
                            >
                                إزالة
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- رسالة توضيحية -->
        <div class="text-sm text-gray-600 bg-blue-50 p-3 rounded-md">
            <p class="font-medium mb-1">تعليمات الاستخدام:</p>
            <ul class="list-disc list-inside space-y-1">
                <li>اكتب على الأقل حرفين للبحث عن الطلاب</li>
                <li>يمكنك البحث باسم الطالب أو كود الطالب أو رقم الطالب</li>
                <li>اضغط على الطالب لإضافته إلى القائمة</li>
                <li>يمكنك إضافة عدة طلاب</li>
            </ul>
        </div>
    </div>

    <script>
        function studentSelector(config) {
            return {
                search: '',
                searchResults: [],
                selectedStudents: [],
                showResults: false,
                state: config.state,

                init() {
                    this.loadSelectedStudents();
                    this.$watch('state', () => this.loadSelectedStudents());
                },

                async searchStudents() {
                    if (this.search.length < 2) {
                        this.searchResults = [];
                        this.showResults = false;
                        return;
                    }

                    try {
                        const response = await fetch(`${config.searchUrl}?search=${encodeURIComponent(this.search)}`);
                        const data = await response.json();
                        
                        // فلترة النتائج لإزالة الطلاب المحددين مسبقاً
                        this.searchResults = data.filter(student => 
                            !this.selectedStudents.some(selected => selected.id === student.id)
                        );
                        
                        this.showResults = true;
                    } catch (error) {
                        console.error('خطأ في البحث:', error);
                        this.searchResults = [];
                        this.showResults = false;
                    }
                },

                selectStudent(student) {
                    // التحقق من عدم وجود الطالب مسبقاً
                    if (this.selectedStudents.some(selected => selected.id === student.id)) {
                        return;
                    }

                    this.selectedStudents.push(student);
                    this.updateState();
                    
                    // مسح البحث
                    this.search = '';
                    this.searchResults = [];
                    this.showResults = false;
                },

                removeStudent(index) {
                    this.selectedStudents.splice(index, 1);
                    this.updateState();
                },

                updateState() {
                    this.state = this.selectedStudents.map(student => student.id);
                },

                loadSelectedStudents() {
                    if (!this.state || !Array.isArray(this.state)) {
                        this.selectedStudents = [];
                        return;
                    }

                    // تحميل بيانات الطلاب المحددين
                    // هنا يمكن إضافة استدعاء API لجلب بيانات الطلاب إذا لزم الأمر
                    // لكن في الوقت الحالي سنحافظ على البيانات الموجودة
                }
            }
        }
    </script>
</x-dynamic-component>