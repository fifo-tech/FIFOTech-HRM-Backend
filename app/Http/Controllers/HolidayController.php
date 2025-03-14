<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Holiday;

class HolidayController extends Controller
{
    public function holidaysList()
    {
        return response()->json(Holiday::orderBy('date')->get());
    }

    public function createHoliday(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'date' => 'required|date|unique:holidays,date',
            'description' => 'nullable|string',
        ]);

        $holiday = Holiday::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Holiday created successfully',
            'holiday' => $holiday]);
    }

    public function updateHoliday(Request $request, $id)
    {
        $holiday = Holiday::findOrFail($id);

        $request->validate([
            'name' => 'required|string',
            'date' => "required|date|unique:holidays,date,$id",
            'description' => 'nullable|string',
        ]);

        $holiday->update($request->all());

        return response()->json(['message' => 'Holiday updated successfully', 'holiday' => $holiday]);
    }

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

    public function deleteHoliday($id)
    {
        $holiday = Holiday::findOrFail($id);
        $holiday->delete();

        return response()->json(['message' => 'Holiday deleted successfully']);
    }
}
