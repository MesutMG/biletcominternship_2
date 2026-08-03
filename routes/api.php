<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Controller;

Route::get('/', [Controller::class, 'tabloIstegi']);
Route::get('/table', [Controller::class, 'tabloIstegi']);
Route::put('/table', [Controller::class, 'cellEdit']);
Route::post('/table/add-column', [Controller::class, 'addColumn']);
Route::post('/table/add-row', [Controller::class, 'addRow']);
Route::post('/table/add-page', [Controller::class, 'addPage']);
