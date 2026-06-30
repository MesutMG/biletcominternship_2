<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;

Route::get('/students', [StudentController::class, 'tabloIstegi']);           //tabloIstegi
Route::post('/students', [StudentController::class, 'ogrenciEkle']);          //ogrenciEkle
Route::put('/students/{id}', [StudentController::class, 'ogrenciEdit']);     //ogrenciEdit
Route::delete('/students/{id}', [StudentController::class, 'ogrenciSil']); //ogrenciSil