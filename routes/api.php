<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Controller;

Route::get('/table', [Controller::class, 'tabloIstegi']);
Route::put('/table', [Controller::class, 'cellEdit']);