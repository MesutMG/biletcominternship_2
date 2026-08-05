<?php
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('files');
});

Route::get('/files', function () {
    return view('files');
});

Route::get('/table', function () {
    return view('index');
});