<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/files', [\App\Http\Controllers\FileController::class, 'index'])->name('files.index');
Route::get('/files/create', [\App\Http\Controllers\FileController::class, 'create'])->name('files.create');
Route::post('/files', [\App\Http\Controllers\FileController::class, 'store'])->name('files.store');

Route::get('/files/{id}', [\App\Http\Controllers\FileController::class, 'show'])->name('files.show');
Route::get('/files/{id}/edit', [\App\Http\Controllers\FileController::class, 'edit'])->name('files.edit');
Route::put('/files/{id}', [\App\Http\Controllers\FileController::class, 'update'])->name('files.update');
Route::delete('/files/{id}', [\App\Http\Controllers\FileController::class, 'destroy'])->name('files.destroy');



Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('auth');

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return response()->json(['message' => 'Logged out'], 200);
})->name('logout');
