<x-filament::page>
    <div class="flex items-center justify-center h-screen">
        <form method="POST" action="{{ route('login') }}" class="w-full max-w-sm">
            @csrf
            <h2 class="mb-6 text-2xl font-bold text-center">تسجيل الدخول</h2>

            <div class="mb-4">
                <label for="phone" class="block text-sm font-medium text-gray-700">رقم الجوال</label>
                <input type="tel" name="phone" required class="mt-1 block w-full border border-gray-300 rounded-md p-2">
            </div>

            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700">كلمة المرور</label>
                <input type="password" name="password" required class="mt-1 block w-full border border-gray-300 rounded-md p-2">
            </div>

            <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded-md">تسجيل الدخول</button>

            @if ($errors->any())
                <div class="mt-4 text-red-500 text-sm text-center">{{ $errors->first() }}</div>
            @endif
        </form>
    </div>
</x-filament::page>