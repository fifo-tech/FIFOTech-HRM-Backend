<?php
//
//namespace App\Http\Controllers;
//
//use Illuminate\Http\Request;
//use App\Models\Holiday;
//use Carbon\Carbon;
//
//class HolidayController extends Controller
//{
//    public function holidaysList()
//    {
//        return response()->json(Holiday::orderBy('date')->get());
//    }
//
//    public function createHoliday(Request $request)
//    {
//        $request->validate([
//            'name' => 'required|string',
//            'date' => 'required|date|unique:holidays,date',
//            'description' => 'nullable|string',
//        ]);
//
//        $holiday = Holiday::create($request->all());
//
//        return response()->json([
//            'success' => true,
//            'message' => 'Holiday created successfully',
//            'holiday' => $holiday]);
//    }
//
//    public function updateHoliday(Request $request, $id)
//    {
//        $holiday = Holiday::findOrFail($id);
//
//        $request->validate([
//            'name' => 'required|string',
//            'date' => "required|date|unique:holidays,date,$id",
//            'description' => 'nullable|string',
//        ]);
//
//        $holiday->update($request->all());
//
//        return response()->json(['message' => 'Holiday updated successfully', 'holiday' => $holiday]);
//    }
//
//    public function getHolidayDetails($id)
//    {
//        try {
//            $holiday = Holiday::findOrFail($id);
//
//            return response()->json([
//                'success' => true,
//                'data' => [
//                    'holiday' => $holiday,
//                ]
//            ]);
//        } catch (\Exception $e) {
//            return response()->json([
//                'success' => false,
//                'message' => 'Holiday not found.',
//            ], 404);
//        }
//    }
//
//    public function deleteHoliday($id)
//    {
//        $holiday = Holiday::findOrFail($id);
//        $holiday->delete();
//
//        return response()->json(['message' => 'Holiday deleted successfully']);
//    }
//
//    public function upcomingHolidays()
//    {
//        $today = Carbon::today();
//        $endDate = Carbon::today()->addDays(30);
//
//        $holidays = Holiday::whereBetween('date', [$today, $endDate])
//            ->orderBy('date')
//            ->get();
//
//        return response()->json([
//            'success' => true,
//            'upcoming_holidays' => $holidays,
//        ]);
//    }
//
//}


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Holiday;
use Carbon\Carbon;

class HolidayController extends Controller
{
    /**
     * Show all holidays (sorted by start_date).
     */
    public function holidaysList()
    {
        return response()->json(Holiday::orderBy('start_date')->get());
    }

    /**
     * Create a new holiday.
     */
    public function createHoliday(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'description' => 'nullable|string',
        ]);

        $holiday = Holiday::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Holiday created successfully',
            'holiday' => $holiday
        ]);
    }

    /**
     * Update an existing holiday.
     */
    public function updateHoliday(Request $request, $id)
    {
        $holiday = Holiday::findOrFail($id);

        $request->validate([
            'name' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'description' => 'nullable|string',
        ]);

        $holiday->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Holiday updated successfully',
            'holiday' => $holiday
        ]);
    }

    /**
     * Get holiday details by ID.
     */
    public function getHolidayDetails($id)
    {
        try {
            $holiday = Holiday::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'holiday' => $holiday,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Holiday not found.',
            ], 404);
        }
    }

    /**
     * Delete a holiday by ID.
     */
    public function deleteHoliday($id)
    {
        $holiday = Holiday::findOrFail($id);
        $holiday->delete();

        return response()->json([
            'success' => true,
            'message' => 'Holiday deleted successfully'
        ]);
    }

    /**
     * Get upcoming holidays within next 30 days.
     */
    public function upcomingHolidays()
    {
        $today = Carbon::today();
        $endDate = Carbon::today()->addDays(30);

        $holidays = Holiday::where(function ($query) use ($today, $endDate) {
            $query->whereBetween('start_date', [$today, $endDate])
                ->orWhereBetween('end_date', [$today, $endDate])
                ->orWhere(function ($q) use ($today, $endDate) {
                    $q->where('start_date', '<=', $today)
                        ->where('end_date', '>=', $endDate);
                });
        })
            ->orderBy('start_date')
            ->get();

        return response()->json([
            'success' => true,
            'upcoming_holidays' => $holidays,
        ]);
    }
}

