<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Call - نظام النداء الذكي للمدارس</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&display=swap');
        body { font-family: 'Tajawal', sans-serif; }
    </style>
</head>
<body class="bg-white">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg fixed w-full top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <div class="bg-blue-600 text-white px-4 py-2 rounded-lg font-bold text-xl">
                            Smart Call
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-8 space-x-reverse">
                    <a href="#home" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">الرئيسية</a>
                    <a href="#features" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">المميزات</a>
                    <a href="#about" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">حولنا</a>
                    <a href="#contact" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">اتصل بنا</a>
                    <a href="/admin" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700">لوحة التحكم</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="pt-20 bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex items-center">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div>
                    <h1 class="text-5xl lg:text-6xl font-bold text-gray-900 mb-6">
                        نظم عملياتك المدرسية بطريقة 
                        <span class="text-blue-600">آمنة وذكية</span>
                    </h1>
                    <p class="text-xl text-gray-600 mb-8 leading-relaxed">
                        Smart Call يوفر لك تجربة فريدة في إدارة حركة الطلاب في الحضور والخروج والمواصلات
                        بنظام النداء الذكي المطور خصيصاً للمدارس
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="#demo" class="bg-blue-600 text-white px-8 py-4 rounded-lg text-lg font-medium hover:bg-blue-700 transition duration-300 text-center">
                            احصل على عرض تجريبي
                        </a>
                        <a href="#features" class="border-2 border-blue-600 text-blue-600 px-8 py-4 rounded-lg text-lg font-medium hover:bg-blue-600 hover:text-white transition duration-300 text-center">
                            تعرف على المميزات
                        </a>
                    </div>
                </div>
                <div class="relative">
                    <div class="bg-white rounded-2xl shadow-2xl p-8 transform rotate-3 hover:rotate-0 transition duration-500">
                        <div class="bg-blue-600 text-white p-6 rounded-xl mb-6">
                            <i class="bi bi-megaphone text-4xl mb-4"></i>
                            <h3 class="text-2xl font-bold">نظام النداء الذكي</h3>
                            <p class="mt-2">أتمتة كاملة لعملية نداء الطلاب</p>
                        </div>
                        <div class="space-y-4">
                            <div class="flex items-center text-gray-700">
                                <i class="bi bi-check-circle-fill text-green-500 ml-3"></i>
                                <span>نداء تلقائي للطلاب</span>
                            </div>
                            <div class="flex items-center text-gray-700">
                                <i class="bi bi-check-circle-fill text-green-500 ml-3"></i>
                                <span>تتبع الحضور والغياب</span>
                            </div>
                            <div class="flex items-center text-gray-700">
                                <i class="bi bi-check-circle-fill text-green-500 ml-3"></i>
                                <span>إدارة الباصات والمسارات</span>
                            </div>
                            <div class="flex items-center text-gray-700">
                                <i class="bi bi-check-circle-fill text-green-500 ml-3"></i>
                                <span>تقارير شاملة</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">مميزات النظام</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    نوفر حلول شاملة لإدارة العمليات المدرسية بطريقة عملية وفعالة
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-8 rounded-2xl text-center hover:shadow-xl transition duration-300">
                    <div class="bg-blue-600 text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="bi bi-megaphone text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">نظام النداء الذكي</h3>
                    <p class="text-gray-600 leading-relaxed">
                        نظام النداء الذكي يوفر خروج آمن للطلاب وتخفيف للازدحام ووقت الانتظار
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-gradient-to-br from-green-50 to-green-100 p-8 rounded-2xl text-center hover:shadow-xl transition duration-300">
                    <div class="bg-green-600 text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="bi bi-clipboard-check text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">نظام الحضور والغياب</h3>
                    <p class="text-gray-600 leading-relaxed">
                        نظام الحضور والانصراف جزء أساسي من إدارة حركة الطلاب ويضمن حضور آمن للجميع
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-8 rounded-2xl text-center hover:shadow-xl transition duration-300">
                    <div class="bg-purple-600 text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="bi bi-bus-front text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">نظام الحافلات</h3>
                    <p class="text-gray-600 leading-relaxed">
                        أحدث نظام لإدارة الحافلات المدرسية مع ميزة التتبع وتصميم المسارات
                    </p>
                </div>

                <!-- Feature 4 -->
                <div class="bg-gradient-to-br from-orange-50 to-orange-100 p-8 rounded-2xl text-center hover:shadow-xl transition duration-300">
                    <div class="bg-orange-600 text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="bi bi-chat-dots text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">نظام التواصل</h3>
                    <p class="text-gray-600 leading-relaxed">
                        نظام رسائل يساعد المدرسة على إبقاء أولياء الأمور على اطلاع دائم بالمستجدات
                    </p>
                </div>

                <!-- Feature 5 -->
                <div class="bg-gradient-to-br from-red-50 to-red-100 p-8 rounded-2xl text-center hover:shadow-xl transition duration-300">
                    <div class="bg-red-600 text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="bi bi-shield-check text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">أمان عالي</h3>
                    <p class="text-gray-600 leading-relaxed">
                        رفع مستوى الأمان في عملية الانصراف عن طريق العديد من خيارات التحكم والمتابعة
                    </p>
                </div>

                <!-- Feature 6 -->
                <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 p-8 rounded-2xl text-center hover:shadow-xl transition duration-300">
                    <div class="bg-indigo-600 text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="bi bi-graph-up text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">تقارير شاملة</h3>
                    <p class="text-gray-600 leading-relaxed">
                        إنتاج تقارير مفصلة عن الحضور والغياب وحركة الطلاب مع إمكانية الطباعة
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">فعالية غير مسبوقة</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    أبناؤنا أمانة لا تقدر بثمن.. ولتوفير بيئة تدعم تطويرهم قمنا بابتكار Smart Call
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="bg-blue-600 text-white w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="bi bi-clock text-3xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">انتظار أقل</h3>
                    <p class="text-gray-600 leading-relaxed">
                        بمساعدة Smart Call، يمكنك توفير ما يصل إلى 50% من إجمالي الوقت المستغرق في عملية الانصراف
                    </p>
                </div>

                <div class="text-center">
                    <div class="bg-green-600 text-white w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="bi bi-people text-3xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">تواصل أسهل</h3>
                    <p class="text-gray-600 leading-relaxed">
                        سهولة التواصل بين إدارة المدرسة وأولياء الأمور عن طريق تنبيهات ورسائل البرنامج المجانية
                    </p>
                </div>

                <div class="text-center">
                    <div class="bg-red-600 text-white w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="bi bi-shield-lock text-3xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">أمان أعلى</h3>
                    <p class="text-gray-600 leading-relaxed">
                        رفع مستوى الأمان في عملية الانصراف عن طريق العديد من خيارات التحكم والمتابعة
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section id="demo" class="py-20 bg-blue-600">
        <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
            <h2 class="text-4xl font-bold text-white mb-6">
                اتصل بنا للحصول على استشارة مجانية
            </h2>
            <p class="text-xl text-blue-100 mb-8 leading-relaxed">
                سيساعدك فريق مهندسي Smart Call على الوصول لأفضل طريقة لتنظيم وحوكمة انصراف الطلاب من مدرستك
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="tel:+966553558839" class="bg-white text-blue-600 px-8 py-4 rounded-lg text-lg font-medium hover:bg-gray-100 transition duration-300">
                    اتصل الآن
                </a>
                <a href="/admin" class="border-2 border-white text-white px-8 py-4 rounded-lg text-lg font-medium hover:bg-white hover:text-blue-600 transition duration-300">
                    دخول لوحة التحكم
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <div class="bg-blue-600 text-white px-4 py-2 rounded-lg font-bold text-xl mb-6 inline-block">
                        Smart Call
                    </div>
                    <p class="text-gray-300 leading-relaxed">
                        نظام النداء الذكي للمدارس - حلول متكاملة لإدارة العمليات المدرسية
                    </p>
                </div>
                <div>
                    <h3 class="text-lg font-bold mb-4">الموقع</h3>
                    <ul class="space-y-2">
                        <li><a href="#home" class="text-gray-300 hover:text-white">الرئيسية</a></li>
                        <li><a href="#features" class="text-gray-300 hover:text-white">المميزات</a></li>
                        <li><a href="#about" class="text-gray-300 hover:text-white">حولنا</a></li>
                        <li><a href="/admin" class="text-gray-300 hover:text-white">لوحة التحكم</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-bold mb-4">الخدمات</h3>
                    <ul class="space-y-2">
                        <li><span class="text-gray-300">نظام النداء الذكي</span></li>
                        <li><span class="text-gray-300">إدارة الحضور والغياب</span></li>
                        <li><span class="text-gray-300">نظام الحافلات</span></li>
                        <li><span class="text-gray-300">نظام التواصل</span></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-bold mb-4">التواصل معنا</h3>
                    <ul class="space-y-2">
                        <li class="text-gray-300">
                            <i class="bi bi-envelope ml-2"></i>
                            info@smartcall.com
                        </li>
                        <li class="text-gray-300">
                            <i class="bi bi-phone ml-2"></i>
                            +966 55 355 8839
                        </li>
                        <li class="text-gray-300">
                            <i class="bi bi-whatsapp ml-2"></i>
                            واتساب
                        </li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-300">
                <p>&copy; 2025 Smart Call. جميع الحقوق محفوظة.</p>
            </div>
        </div>
    </footer>

    <script>
        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Add scrolled class to navigation
        window.addEventListener('scroll', function() {
            const nav = document.querySelector('nav');
            if (window.scrollY > 100) {
                nav.classList.add('bg-white', 'shadow-lg');
            } else {
                nav.classList.remove('bg-white', 'shadow-lg');
            }
        });
    </script>
</body>
</html>