<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuestoesController;
use App\Http\Controllers\LoginRegisterController;
use App\Http\Controllers\PasswordResetController;



Route::post("/registrar", [LoginRegisterController::class, "registrar"]);
Route::post("/login", [LoginRegisterController::class, "login"]);


Route::middleware(['auth:sanctum'])->group(function() {
    Route::get("/buscar-gerar-questoes-tema", [QuestoesController::class, 'buscarGerarQuestoesTema'])->name('buscarGerarQuestoesTema');
    Route::post("/logout", [LoginRegisterController::class, "logout"])->name('logout');
});
    
Route::post('/forgot-password', [PasswordResetController::class, 'sendToken']);
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);

