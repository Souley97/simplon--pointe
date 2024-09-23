<?php

namespace App\Http\Controllers;

abstract class Controller
{
    public static function CustomJsonResponse($message, $data, $status = 200)
    {
      return response()->json(['message' => $message, 'data' => $data], $status);
    }
}
