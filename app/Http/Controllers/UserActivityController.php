<?php
//
//namespace App\Http\Controllers;
//use App\Models\UserActivityLog;
//use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Auth;
//
//class UserActivityController extends Controller
//{
//    public function logActivity(Request $request)
//    {
//        if (Auth::check()) {
//            $user = Auth::user();
//            UserActivityLog::create([
//                'user_id' => $user->id,
//                'activity' => $request->input('activity'),
//                'details' => json_encode($request->input('details')),
//                'ip_address' => $request->ip(),
//            ]);
//            return response()->json(['message' => 'Activity logged successfully']);
//        }
//
//        return response()->json(['message' => 'User not authenticated'], 401);
//    }
//
//    public function getActivityLogs()
//    {
//        $logs = UserActivityLog::with('user')->latest()->paginate(10);
//        return response()->json($logs);
//    }
//
//}
