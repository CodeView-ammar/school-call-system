
<div class="space-y-4">
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <h3 class="text-sm font-medium text-blue-800 mb-2">الحقول المطلوبة (يجب توفرها):</h3>
        <ul class="text-xs text-blue-700 space-y-1">
            <li><strong>student_code</strong> - كود الطالب (فريد)</li>
            <li><strong>student_name_ar</strong> - اسم الطالب بالعربية</li>
            <li><strong>branch_name</strong> - اسم الفرع (يجب أن يكون موجود في النظام)</li>
            <li><strong>academic_band_name</strong> - اسم الفرقة الأكاديمية (يجب أن تكون موجودة في النظام)</li>
            <li><strong>grade_class_name</strong> - اسم الفصل الدراسي (يجب أن يكون موجود في النظام)</li>
        </ul>
    </div>
    
    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
        <h3 class="text-sm font-medium text-green-800 mb-2">الحقول الاختيارية:</h3>
        <div class="text-xs text-green-700 space-y-1">
            <div class="grid grid-cols-2 gap-2">
                <ul class="space-y-1">
                    <li><strong>student_number</strong> - الرقم الأكاديمي</li>
                    <li><strong>student_name_en</strong> - الاسم بالإنجليزية</li>
                    <li><strong>national_id</strong> - رقم الهوية</li>
                    <li><strong>date_of_birth</strong> - تاريخ الميلاد (YYYY-MM-DD)</li>
                    <li><strong>gender</strong> - الجنس (ذكر/أنثى)</li>
                    <li><strong>nationality</strong> - الجنسية</li>
                    <li><strong>address_ar</strong> - العنوان بالعربية</li>
                    <li><strong>address_en</strong> - العنوان بالإنجليزية</li>
                    <li><strong>latitude</strong> - خط العرض</li>
                    <li><strong>longitude</strong> - خط الطول</li>
                </ul>
                <ul class="space-y-1">
                    <li><strong>medical_notes</strong> - الملاحظات الطبية</li>
                    <li><strong>emergency_contact</strong> - جهة اتصال الطوارئ</li>
                    <li><strong>pickup_location</strong> - مكان الاستقلال</li>
                    <li><strong>bus_code</strong> - كود الحافلة</li>
                    <li><strong>is_active</strong> - نشط (نعم/لا)</li>
                    <li><strong>guardian_1_name</strong> - اسم ولي الأمر الأول</li>
                    <li><strong>guardian_1_phone</strong> - هاتف ولي الأمر الأول</li>
                    <li><strong>guardian_1_relationship</strong> - علاقة ولي الأمر الأول</li>
                    <li><strong>guardian_2_name</strong> - اسم ولي الأمر الثاني</li>
                    <li><strong>guardian_2_phone</strong> - هاتف ولي الأمر الثاني</li>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <h3 class="text-sm font-medium text-yellow-800 mb-2">ملاحظات هامة:</h3>
        <ul class="text-xs text-yellow-700 space-y-1">
            <li>• يجب أن يكون الصف الأول يحتوي على أسماء الأعمدة باللغة الإنجليزية كما هو موضح أعلاه</li>
            <li>• الفروع والفرق الأكاديمية والفصول يجب أن تكون موجودة مسبقاً في النظام</li>
            <li>• كود الطالب يجب أن يكون فريد ولا يتكرر</li>
            <li>• تنسيق التاريخ يجب أن يكون: YYYY-MM-DD (مثال: 2010-05-15)</li>
            <li>• الجنس: استخدم "ذكر" أو "أنثى" أو "male" أو "female"</li>
            <li>• حالة النشاط: استخدم "نعم" أو "لا" أو "yes" أو "no"</li>
        </ul>
    </div>
</div>
<div class="text-sm text-gray-600 dark:text-gray-400">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- الحقول المطلوبة -->
        <div class="space-y-2">
            <h4 class="font-semibold text-green-700 dark:text-green-400">الحقول المطلوبة:</h4>
            <ul class="list-disc list-inside space-y-1 text-xs">
                <li><span class="font-medium">كود الطالب:</span> رمز فريد لكل طالب</li>
                <li><span class="font-medium">اسم الطالب بالعربية:</span> الاسم الكامل</li>
                <li><span class="font-medium">اسم الفرع:</span> يجب أن يطابق فرع موجود</li>
                <li><span class="font-medium">الفرقة الأكاديمية:</span> يجب أن تطابق فرقة موجودة</li>
                <li><span class="font-medium">الفصل الدراسي:</span> يجب أن يطابق فصل موجود</li>
            </ul>
        </div>

        <!-- الحقول الاختيارية -->
        <div class="space-y-2">
            <h4 class="font-semibold text-blue-700 dark:text-blue-400">الحقول الاختيارية:</h4>
            <ul class="list-disc list-inside space-y-1 text-xs">
                <li>الرقم الأكاديمي</li>
                <li>اسم الطالب بالإنجليزية</li>
                <li>رقم الهوية</li>
                <li>تاريخ الميلاد (YYYY-MM-DD)</li>
                <li>الجنس (ذكر/أنثى)</li>
                <li>الجنسية</li>
                <li>العنوان</li>
                <li>الإحداثيات (خط العرض والطول)</li>
                <li>الملاحظات الطبية</li>
                <li>مكان الاستقلال</li>
                <li>كود الحافلة</li>
                <li>معلومات أولياء الأمور</li>
            </ul>
        </div>
    </div>

    <!-- تنسيق البيانات -->
    <div class="mt-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
        <h4 class="font-semibold text-yellow-800 dark:text-yellow-200 mb-2">ملاحظات مهمة:</h4>
        <ul class="list-disc list-inside space-y-1 text-xs text-yellow-700 dark:text-yellow-300">
            <li>تاريخ الميلاد: استخدم تنسيق YYYY-MM-DD (مثال: 2010-05-15)</li>
            <li>الجنس: ذكر، أنثى، male، أو female</li>
            <li>نشط: نعم، لا، yes، أو no</li>
            <li>تأكد من صحة أسماء الفروع والفصول والفرق الأكاديمية</li>
            <li>لا تغير ترتيب الأعمدة في القالب</li>
        </ul>
    </div>

    <div class="mt-3 p-2 bg-blue-50 dark:bg-blue-900/20 rounded border border-blue-200 dark:border-blue-800">
        <p class="text-xs text-blue-700 dark:text-blue-300">
            <strong>نصيحة:</strong> استخدم زر "تحميل قالب الاستيراد" للحصول على ملف Excel جاهز مع أمثلة وتعليمات مفصلة.
        </p>
    </div>

    <div class="mt-3 p-2 bg-red-50 dark:bg-red-900/20 rounded border border-red-200 dark:border-red-800">
        <p class="text-xs text-red-700 dark:text-red-300">
            <strong>تنبيه هام:</strong> يجب استخدام أسماء الأعمدة الإنجليزية الموجودة في الصف الأول من القالب (مثل student_code، student_name_ar، إلخ). الصف الثاني يحتوي على الترجمة العربية للتوضيح فقط.
        </p>
    </div>
</div>
