<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\UserController;
use App\Models\User;


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::middleware('api')->group(function () {
    Route::get('/users/by-remember-token', function(Request $request) {
        $code = $request->get('code');
        if (!$code || strlen($code) < 8) {
            return response()->json([]);
        }
        $users = User::query()
            ->where('remember_token', $code)
            ->select(['uuid', 'name', 'email'])
            ->get();
        return response()->json($users);
    });
});
