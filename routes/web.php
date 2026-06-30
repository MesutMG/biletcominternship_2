<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GfGController;

Route::get('laravel', function () {
    return view('laravel');
});

Route::get('greeting', function () {
    return 'Hello World';
});

Route::get('app', function () {
    return view('app');
});

Route::get ('emp/{name?}', function ($name = 'Guest') {
    echo $name;
});

Route::get('gfg', [GfGController::class, 'index']);

Route::get('/', function () {
    return view('index');
});