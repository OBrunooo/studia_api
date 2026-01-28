<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuestoesController;
use App\Http\Controllers\LoginRegisterController;
use App\Http\Controllers\TemasController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PasswordResetController;

use App\Models\User;

Route::middleware('api')->group(function () {
    Route::get("/gerarOuBuscarTema", [QuestoesController::class,"verificarTema"])->name("verificaTema.get");
    Route::get("/gerarQuestoes", [QuestoesController::class,"gerarQuestoes"])->name("gerarQuestoes.get");
    Route::get("/verificarQuestoes",[QuestoesController::class, "verificarQuestoes"])->name("verificarQuestoes.get");
    Route::get("/verificarConjunto",[QuestoesController::class, "verificarConjunto"])->name("verificarConjunto.get");
    Route::get("/storageQuestoes",[QuestoesController::class, "storage"])->name("storageQuestoes.get");

    Route::post("/registrar", [LoginRegisterController::class, "registrar"]);
    Route::post("/login", [LoginRegisterController::class, "login"]);
    
    
    Route::middleware(['auth:sanctum'])->group(function() {
        Route::get("/gerarOuBuscarTema", [QuestoesController::class,"verificarTema"])->name("verificaTema.get");
        Route::get("listaConjuntos", [QuestoesController::class, "listaConjuntos"]);
        Route::post("/logout", [LoginRegisterController::class, "logout"]);
        Route::get("/temasUsuario", [TemasController::class, "temas"]);
        Route::get("/quetoesTema", [TemasController::class, "questoes"]);
        Route::post("/conclusaoConjunto", [TemasController::class, "conclusao"]);
        Route::get("/storageQuestoes",[QuestoesController::class, "storage"])->name("storageQuestoes.get");
        Route::post("/atualizaSenha",[UserController::class, "atualizaSenha"]);
        Route::post("/atualizaAvatar",[UserController::class, "atualizaAvatar"]);
        });
        
    Route::post('/forgot-password', [PasswordResetController::class, 'sendToken']);
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);

});
