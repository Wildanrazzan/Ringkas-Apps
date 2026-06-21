<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'application' => 'Money Notes API',
        'version' => '1.0.0',
        'status' => 'online',
    ]);
});
