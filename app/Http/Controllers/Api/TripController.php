<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TripController extends Controller
{
    /**
     * الحصول على معرف المدرسة من المستخدم أو الطلب
     */
    private function getSchoolId(Request $request)
    {
        $user = $request->user();
        
        // Super Admin يمكنه تحديد المدرسة
        if ($user->user_type === 'super_admin' && $request->has('school_id')) {
            return $request->school_id;
        }
        
        // باقي المستخدمين مقيدون بمدرستهم
        return $user->school_id;
    }

    /**
     * جلب جميع الرحلات حسب السائق
     */
    public function getTripsByDriver(Request $request, $driverId) 
{
    // Validate driver ID
    // $validator = Validator::make(['driver_id' => $driverId], [
    //     'driver_id' => 'required|exists:drivers,id'
    // ]);

    // if ($validator->fails()) {
    //     return response()->json([
    //         'message' => 'معرف السائق غير صحيح',
    //         'errors' => $validator->errors()
    //     ], 422);
    // }

    $schoolId = $this->getSchoolId($request);
    
    // Get the driver record and execute the query
    $driver = Driver::where("user_id", $driverId)->first();
    
    // Check if driver exists
    if (!$driver) {
        return response()->json([
            'message' => 'السائق غير موجود',
            'errors' => ['driver_id' => ['Driver not found']]
        ], 404);
    }
    
    $query = Trip::with(['route', 'driver', 'bus', 'school'])
        ->where('driver_id', $driver->id);
        
    if ($request->has('school_id')) {
            
           $query->where('school_id', $schoolId);
    }
    // فلترة حسب النشاط
    if ($request->has('is_active')) {
        $query->where('is_active', $request->boolean('is_active'));
    }

    // فلترة حسب التاريخ
    if ($request->has('effective_date')) {
        $query->where('effective_date', $request->effective_date);
    }

    // ترتيب النتائج
    $sortBy = $request->get('sort_by', 'effective_date');
    $sortOrder = $request->get('sort_order', 'desc');
    $query->orderBy($sortBy, $sortOrder);

    // الحصول على النتائج
    $perPage = min($request->get('per_page', 15), 100);
    $trips = $query->paginate($perPage);

    return response()->json([
        'data' => $trips->items(),
        'meta' => [
            'current_page' => $trips->currentPage(),
            'last_page' => $trips->lastPage(),
            'per_page' => $trips->perPage(),
            'total' => $trips->total(),
        ]
    ]);
}

    /**
     * جلب تفاصيل الرحلة مع نقاط التوقف
     */
    public function getTripDetails(Request $request, $tripId)
    {
        $schoolId = $this->getSchoolId($request);
        
        $trip = Trip::with([
            'route',
            'driver',
            'bus',
            'school',
            'tripStops' => function ($query) {
                $query->orderBy('stop_order');
            },
            'tripStops.stop'
        ])
        ->where('school_id', $schoolId)
        ->find($tripId);

        if (!$trip) {
            return response()->json([
                'message' => 'الرحلة غير موجودة'
            ], 404);
        }

        // إضافة معلومات إضافية عن المحطات
        $trip->tripStops->each(function ($tripStop) {
            $tripStop->stop_details = [
                'name' => $tripStop->stop->name ?? 'محطة غير محددة',
                'address' => $tripStop->stop->address ?? 'عنوان غير محدد',
                'latitude' => $tripStop->stop->latitude,
                'longitude' => $tripStop->stop->longitude,
                'arrival_time' => $tripStop->arrival_time,
                'stop_order' => $tripStop->stop_order,
                'is_pickup' => $tripStop->is_pickup,
                'is_dropoff' => $tripStop->is_dropoff,
            ];
        });

        return response()->json([
            'data' => $trip
        ]);
    }

    /**
     * جلب جميع الرحلات (مع إمكانية التصفية)
     */
    public function index(Request $request)
    {
        $schoolId = $this->getSchoolId($request);
        
        $query = Trip::with(['route', 'driver', 'bus', 'school'])
            ->where('school_id', $schoolId);

        // فلترة حسب السائق
        if ($request->has('driver_id') && $request->driver_id) {
            $query->where('driver_id', $request->driver_id);
        }

        // فلترة حسب الباص
        if ($request->has('bus_id') && $request->bus_id) {
            $query->where('bus_id', $request->bus_id);
        }

        // فلترة حسب المسار
        if ($request->has('route_id') && $request->route_id) {
            $query->where('route_id', $request->route_id);
        }

        // فلترة حسب النشاط
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // فلترة حسب التاريخ
        if ($request->has('effective_date')) {
            $query->where('effective_date', $request->effective_date);
        }

        // البحث النصي
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('route', function ($routeQuery) use ($search) {
                    $routeQuery->where('route_ar', 'like', "%{$search}%")
                             ->orWhere('route_en', 'like', "%{$search}%");
                })
                ->orWhereHas('driver', function ($driverQuery) use ($search) {
                    $driverQuery->where('name_ar', 'like', "%{$search}%")
                               ->orWhere('name_en', 'like', "%{$search}%");
                })
                ->orWhereHas('bus', function ($busQuery) use ($search) {
                    $busQuery->where('number', 'like', "%{$search}%");
                });
            });
        }

        // ترتيب النتائج
        $sortBy = $request->get('sort_by', 'effective_date');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // الحصول على النتائج
        $perPage = min($request->get('per_page', 15), 100);
        $trips = $query->paginate($perPage);

        return response()->json([
            'data' => $trips->items(),
            'meta' => [
                'current_page' => $trips->currentPage(),
                'last_page' => $trips->lastPage(),
                'per_page' => $trips->perPage(),
                'total' => $trips->total(),
            ]
        ]);
    }

    /**
     * إحصائيات الرحلات
     */
    public function statistics(Request $request)
    {
        $schoolId = $this->getSchoolId($request);
        
        $trips = Trip::where('school_id', $schoolId);

        $stats = [
            'total_trips' => $trips->count(),
            'active_trips' => $trips->where('is_active', true)->count(),
            'inactive_trips' => $trips->where('is_active', false)->count(),
            'trips_today' => $trips->where('effective_date', now()->toDateString())->count(),
            'trips_this_week' => $trips->whereBetween('effective_date', [
                now()->startOfWeek()->toDateString(),
                now()->endOfWeek()->toDateString()
            ])->count(),
            'trips_this_month' => $trips->whereBetween('effective_date', [
                now()->startOfMonth()->toDateString(),
                now()->endOfMonth()->toDateString()
            ])->count(),
        ];

        // إحصائيات حسب السائق
        if ($request->has('driver_id') && $request->driver_id) {
            $driverTrips = $trips->where('driver_id', $request->driver_id);
            $stats['driver_stats'] = [
                'total_trips' => $driverTrips->count(),
                'active_trips' => $driverTrips->where('is_active', true)->count(),
                'trips_today' => $driverTrips->where('effective_date', now()->toDateString())->count(),
            ];
        }

        return response()->json($stats);
    }

    /**
     * البحث السريع عن الرحلات
     */
    public function search(Request $request)
    {
        $search = $request->get('search', '');
        
        if (strlen($search) < 2) {
            return response()->json([]);
        }

        $schoolId = $this->getSchoolId($request);
        
        $trips = Trip::with(['route', 'driver', 'bus'])
            ->where('school_id', $schoolId)
            ->where(function ($q) use ($search) {
                $q->whereHas('route', function ($routeQuery) use ($search) {
                    $routeQuery->where('route_ar', 'like', "%{$search}%")
                             ->orWhere('route_en', 'like', "%{$search}%");
                })
                ->orWhereHas('driver', function ($driverQuery) use ($search) {
                    $driverQuery->where('name_ar', 'like', "%{$search}%")
                               ->orWhere('name_en', 'like', "%{$search}%");
                })
                ->orWhereHas('bus', function ($busQuery) use ($search) {
                    $busQuery->where('number', 'like', "%{$search}%");
                });
            })
            ->where('is_active', true)
            ->limit(50)
            ->get()
            ->map(function ($trip) {
                return [
                    'id' => $trip->id,
                    'route_name' => $trip->route->route_ar ?? 'مسار غير محدد',
                    'driver_name' => $trip->driver->name_ar ?? 'سائق غير محدد',
                    'bus_number' => $trip->bus->number ?? 'باص غير محدد',
                    'effective_date' => $trip->effective_date,
                    'arrival_time' => $trip->arrival_time_at_first_stop,
                    'display_name' => "{$trip->route->route_ar} - {$trip->driver->name_ar} - {$trip->effective_date}",
                ];
            });
        
        return response()->json($trips);
    }

    /**
     * جلب الطلاب المرتبطين بالنقاط الخاصة بالرحلة
     */
 public function getStudentsByTrip(Request $request, $tripId)
{
    // التحقق من وجود الرحلة
    $trip = Trip::with(['tripStops.stop.student','branch']) // جلب النقاط والطلاب
        ->find($tripId);

    if (!$trip) {
        return response()->json([
            'message' => 'الرحلة غير موجودة'
        ], 404);
    }

    // إعداد البيانات
    $tripData = [
        'trip_id' => $trip->id,
        'effective_date' => $trip->effective_date,
        'trip_type' => $trip->trip_type,
        'branch' => [
            'id' => $trip->branch->id,
            'name_ar' => $trip->branch->name_ar,
            'name_en' => $trip->branch->name_en,
            'code' => $trip->branch->code,
            'logo' => $trip->branch->logo,
            'address_ar' => $trip->branch->address_ar,
            'address_en' => $trip->branch->address_en,
            'latitude' => $trip->branch->latitude,
            'longitude' => $trip->branch->longitude,
        ],
        'trip_stops' => $trip->tripStops->map(function ($tripStop) {
            return [
                'stop_id' => $tripStop->stop_id,
                'name'       => $tripStop->stop?->name,
                'address'    => $tripStop->stop?->address,
                'latitude'   => $tripStop->stop?->latitude,
                'longitude'  => $tripStop->stop?->longitude,
                'description'=> $tripStop->stop?->description,
                'is_active'  => $tripStop->stop?->is_active,
                'stop_order' => $tripStop->stop_order,
                'students' => $tripStop->stop->student, // تأكد من استخدام العلاقة الصحيحة
            ];
        }),
    ];

    return response()->json($tripData);
}


/**
 * جلب الرحلة مع النقاط والطلاب باستخدام معرف الطالب
 */
public function getTripByStudent(Request $request, $studentId)
{
    // جلب النقاط المرتبطة بالطالب
    $tripStop = \App\Models\TripStop::with(['trip', 'stop', 'trip.driver', 'trip.bus', 'trip.school'])
        ->whereHas('stop.student', function($q) use ($studentId) {
            $q->where('id', $studentId);
        })
        ->first();

    if (!$tripStop) {
        return response()->json([
            'message' => 'الطالب غير مرتبط بأي رحلة حالية'
        ], 404);
    }

    $trip = $tripStop->trip;

    // إضافة معلومات الرحلة مع النقاط
    $tripData = [
        'trip_id' => $trip->id,
        'effective_date' => $trip->effective_date,
        'trip_type' => $trip->trip_type,
        'driver' => [
            'id' => $trip->driver->id,
            'name' => $trip->driver->name ?? 'غير محدد',
        ],
        'bus' => [
            'id' => $trip->bus->id,
            'number' => $trip->bus->number ?? 'غير محدد',
        ],
        'school' => [
            'id' => $trip->school->id,
            'name' => $trip->school->name_ar ?? 'غير محدد',
        ],
        'trip_stops' => $trip->tripStops->map(function ($stop) {
            return [
                'stop_id' => $stop->stop_id,
                'name' => $stop->stop?->name,
                'address' => $stop->stop?->address,
                'latitude' => $stop->stop?->latitude,
                'longitude' => $stop->stop?->longitude,
                'stop_order' => $stop->stop_order,
                'students' => $stop->stop?->student, // علاقة الطلاب في المحطة
            ];
        }),
    ];

    return response()->json($tripData);
}



}
