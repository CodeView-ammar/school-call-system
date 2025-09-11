<div class="space-y-4">
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <h3 class="text-sm font-medium text-blue-800 mb-2">الحقول المطلوبة (يجب توفرها):</h3>
        <ul class="text-xs text-blue-700 space-y-1">
            <li><strong>code / كود_الطالب</strong> - كود الطالب (فريد لكل مدرسة)</li>
            <li><strong>name_ar / اسم_الطالب / الاسم_العربي</strong> - اسم الطالب بالعربية</li>
            <li><strong>branch_name / اسم_الفرع</strong> - اسم الفرع (أو تحديد فرع افتراضي)</li>
            <li><strong>academic_band_name / اسم_المرحلة</strong> - اسم الفرقة الأكاديمية (مطلوب)</li>
            <li><strong>grade_class_name / اسم_الصف</strong> - اسم الفصل الدراسي (مطلوب)</li>
        </ul>
    </div>

    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
        <h3 class="text-sm font-medium text-green-800 mb-2">الحقول الاختيارية:</h3>
        <div class="text-xs text-green-700 space-y-1">
            <div class="grid grid-cols-2 gap-2">
                <ul class="space-y-1">
                    <li><strong>student_number / رقم_الطالب</strong> - الرقم الأكاديمي</li>
                    <li><strong>name_en / الاسم_الانجليزي</strong> - الاسم بالإنجليزية</li>
                    <li><strong>national_id / الرقم_الوطني</strong> - رقم الهوية</li>
                    <li><strong>date_of_birth / تاريخ_الميلاد</strong> - تاريخ الميلاد</li>
                    <li><strong>gender / الجنس</strong> - الجنس (male/female)</li>
                    <li><strong>nationality / الجنسية</strong> - الجنسية</li>
                    <li><strong>address_ar / العنوان_العربي</strong> - العنوان بالعربية</li>
                    <li><strong>address_en / العنوان_الانجليزي</strong> - العنوان بالإنجليزية</li>
                </ul>
                <ul class="space-y-1">
                    <li><strong>latitude / خط_العرض</strong> - الإحداثيات</li>
                    <li><strong>longitude / خط_الطول</strong> - الإحداثيات</li>
                    <li><strong>medical_notes / الملاحظات_الطبية</strong> - ملاحظات طبية</li>
                    <li><strong>emergency_contact / جهة_الاتصال_الطارئ</strong> - جهة اتصال طارئ</li>
                    <li><strong>pickup_location / مكان_الاستقلال</strong> - مكان الاستقلال</li>
                    <li><strong>bus_code / كود_الحافلة</strong> - كود الحافلة</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- معلومات أولياء الأمور -->
    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
        <h3 class="text-sm font-medium text-purple-800 mb-2">معلومات أولياء الأمور (اختيارية):</h3>
        <div class="text-xs text-purple-700 space-y-1">
            <p class="mb-2">للولي الأول:</p>
            <ul class="space-y-1">
                    <li><strong>guardian_name_ar / اسم_ولي_الامر_عربي</strong></li>
                    <li><strong>guardian_phone / هاتف_ولي_الامر</strong></li>
                    <li><strong>guardian_email / ايميل_ولي_الامر</strong></li>
                    <li><strong>guardian_relationship / صلة_القرابة</strong></li>
                </ul>
                <p class="mt-2 mb-2">للولي الثاني (إضافة البادئة "2_"):</p>
                <ul class="list-disc list-inside space-y-1 ml-4">
                    <li><strong>2_guardian_name_ar / 2_اسم_ولي_الامر_عربي</strong></li>
                    <li><strong>2_guardian_phone / 2_هاتف_ولي_الامر</strong></li>
                    <li><strong>2_guardian_relationship / 2_صلة_القرابة</strong></li>
                </ul>
        </div>
    </div>

    <!-- تنسيق البيانات -->
    <div class="mt-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
        <h4 class="font-semibold text-yellow-800 dark:text-yellow-200 mb-2">ملاحظات مهمة:</h4>
        <ul class="list-disc list-inside space-y-1 text-xs text-yellow-700 dark:text-yellow-300">
            <li>يجب أن يكون كود الطالب فريداً لكل طالب في نفس المدرسة</li>
            <li>اسم الطالب بالعربية مطلوب</li>
            <li>يجب تحديد فرع افتراضي أو إضافة عمود الفرع في الملف</li>
            <li>الفرقة الأكاديمية والفصل الدراسي مطلوبان</li>
            <li>يجب أن تتطابق أسماء الفروع والفرق والفصول مع ما هو موجود في النظام</li>
            <li>يمكن استخدام الأسماء العربية أو الإنجليزية للأعمدة</li>
            <li>الحقول الفارغة سيتم تجاهلها</li>
            <li>تاريخ الميلاد يجب أن يكون بتنسيق صحيح (YYYY-MM-DD أو DD/MM/YYYY)</li>
        </ul>
    </div>
</div>