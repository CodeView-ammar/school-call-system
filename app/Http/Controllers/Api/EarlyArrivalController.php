<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EarlyArrival;
use Illuminate\Http\Request;
use App\Http\Resources\EarlyArrivalResource;

class EarlyArrivalController extends Controller
{
    public function index()
    {
        $earlyArrivals = EarlyArrival::with(['student', 'guardian', 'school', 'branch', 'user'])->paginate(15);
        return EarlyArrivalResource::collection($earlyArrivals);
    }

    public function show($id)
    {
        $earlyArrival = EarlyArrival::with(['student', 'guardian', 'school', 'branch', 'user'])->findOrFail($id);
        return new EarlyArrivalResource($earlyArrival);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'guardian_id' => 'nullable|exists:guardians,id',
            'school_id' => 'required|exists:schools,id',
            'branch_id' => 'required|exists:branches,id',
            'user_id' => 'nullable|exists:users,id',
            'pickup_date' => 'required|date',
            'pickup_time' => 'required',
            'pickup_reason' => 'nullable|string',
            'status' => 'required|in:pending,approved,rejected,completed,canceled',
        ]);

        $earlyArrival = EarlyArrival::create($validated);

        return new EarlyArrivalResource($earlyArrival);
    }

    public function update(Request $request, $id)
    {
        $earlyArrival = EarlyArrival::findOrFail($id);

        $validated = $request->validate([
            'student_id' => 'sometimes|required|exists:students,id',
            'guardian_id' => 'nullable|exists:guardians,id',
            'school_id' => 'sometimes|required|exists:schools,id',
            'branch_id' => 'sometimes|required|exists:branches,id',
            'user_id' => 'nullable|exists:users,id',
            'pickup_date' => 'sometimes|required|date',
            'pickup_time' => 'sometimes|required',
            'pickup_reason' => 'nullable|string',
            'status' => 'sometimes|required|in:pending,approved,rejected,completed,canceled',
        ]);

        $earlyArrival->update($validated);

        return new EarlyArrivalResource($earlyArrival);
    }

    public function destroy($id)
    {
        $earlyArrival = EarlyArrival::findOrFail($id);
        $earlyArrival->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}
