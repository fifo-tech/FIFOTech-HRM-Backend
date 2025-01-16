<?php

namespace App\Http\Controllers;

abstract class Controller
{
    public function response($success, $message, $data, $code) {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => $data,
            'code' => $code,
            ]);
        }
}
