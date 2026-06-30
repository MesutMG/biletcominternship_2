<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;

Route::get('/', function () {
    return view('index');
});

Route::get('/students', [StudentController::class, 'index']);   // Replaces tabloIstegi
Route::post('/students', [StudentController::class, 'store']);  // Replaces ogrenciEkle
Route::put('/students/{id}', [StudentController::class, 'update']); // Replaces ogrenciEdit
Route::delete('/students/{id}', [StudentController::class, 'destroy']); // Replaces ogrenciSil