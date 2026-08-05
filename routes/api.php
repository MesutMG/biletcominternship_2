<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Controller;

Route::post('/files', [Controller::class, 'getFiles']);
Route::post('/files/create', [Controller::class, 'createFile']);
Route::post('/files/delete', [Controller::class, 'deleteFile']);

Route::post('/table/get-page', [Controller::class, 'getPage']);
Route::post('/table/add-page', [Controller::class, 'addPage']);
Route::post('/table/delete-page', [Controller::class, 'deletePage']);

Route::post('/table/add-row', [Controller::class, 'addRow']);
Route::post('/table/insert-row', [Controller::class, 'insertRow']);
Route::post('/table/delete-row', [Controller::class, 'deleteRow']);

Route::post('/table/add-column', [Controller::class, 'addColumn']);
Route::post('/table/insert-column', [Controller::class, 'insertColumn']);
Route::post('/table/delete-column', [Controller::class, 'deleteColumn']);

Route::post('/table/edit-cell', [Controller::class, 'editCell']);