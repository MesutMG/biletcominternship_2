<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('gfg', function () {
    return view('gfg');
});

Route::get('/greeting', function () {
    return 'Hello World';
});
