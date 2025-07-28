    <script>
        function printDiv(divId) {
            alert('جاري تجهيز الطباعة...');
            const content = document.getElementById(divId).innerHTML;
            const myWindow = window.open('', 'Print', 'width=800,height=600');
            myWindow.document.write('<html><head><title>طباعة تفاصيل النداء</title>');
            myWindow.document.write('<style>body{font-family: Arial, sans-serif; direction: rtl; padding: 20px;} table {width: 100%; border-collapse: collapse;} th, td {border: 1px solid #ccc; padding: 8px;} th {background-color: #f0f0f0;}</style>');
            myWindow.document.write('</head><body >');
            myWindow.document.write(content);
            myWindow.document.write('</body></html>');
            myWindow.document.close();
            myWindow.focus();
            myWindow.print();
            myWindow.close();
        }
    </script>
<div id="printable-area" class="space-y-4 p-4">
    <h2 class="text-lg font-bold">معلومات نداء الطالب</h2>
    <div><strong>اسم الطالب:</strong> {{ $record->student->name_ar ?? '-' }}</div>
    <div><strong>اسم المستخدم:</strong> {{ $record->user->name ?? '-' }}</div>
    <div><strong>نوع النداء:</strong> {{ $record->call_level == 'urgent' ? 'نداء مستعجل' : 'نداء عادي' }}</div>
    <div><strong>تاريخ النداء:</strong> {{ $record->call_cdate }}</div>
    <div><strong>الحالة:</strong> 
        @php
            $labels = [
                'prepare' => 'طلب الاستعداد',
                'leave' => 'طلب المغادرة',
                'with_teacher' => 'مع المعلم',
                'to_gate' => 'في الطريق إلى البوابة',
                'received' => 'تم استلام الطالب',
                'canceled' => 'إلغاء',
                'homeward' => 'في طريق العودة',
                'arrived_home' => 'وصل إلى المنزل',
                'delivered' => 'تم التسليم',
            ];
        @endphp
        {{ $labels[$record->status] ?? $record->status }}
    </div>

    <div class="p-4">
        <h2 class="text-lg font-bold mb-2">تفاصيل التغييرات على النداء</h2>
        @if($record->studentCallLogs->count())
            <table class="min-w-full bg-white border rounded">
                <thead>
                    <tr class="bg-gray-100 text-left">
                        <th class="py-2 px-4 border-b">الحالة</th>
                        <th class="py-2 px-4 border-b">تم التغيير بواسطة</th>
                        <th class="py-2 px-4 border-b">وقت التغيير</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($record->studentCallLogs as $log)
                        <tr>
                            <td class="py-2 px-4 border-b">{{ $labels[$log->status] ?? $log->status }}</td>
                            <td class="py-2 px-4 border-b">{{ $log->changedByUser?->name ?? '—' }}</td>
                            <td class="py-2 px-4 border-b">{{ $log->changed_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>لا يوجد تغييرات مسجلة على هذا النداء.</p>
        @endif
    </div>
</div>

@if(!empty($printScript) && $printScript)
    <div class="my-4">
        <a href="javascript:void(0)"
            onclick="printDiv('printable-area')"
            class="bg-blue-600 text-black px-4 py-2 rounded hover:bg-blue-700 hover:text-white transition-colors"
        >
            طباعة تفاصيل النداء
        </a>
</div>


@endif