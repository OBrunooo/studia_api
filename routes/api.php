<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuestoesController;
use App\Http\Controllers\LoginRegisterController;
use App\Http\Controllers\TemasController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PasswordResetController;

use App\Models\User;

Route::get("/questoes/gerar", [QuestoesController::class,"gerarQuestoes"])->name("gerarQuestoes.get");
Route::get("/questoes/verificar",[QuestoesController::class, "verificarQuestoes"])->name("verificarQuestoes.get");
Route::get("/conjuntos/verificar",[QuestoesController::class, "verificarConjunto"])->name("verificarConjunto.get");
Route::get("/questoes/storage",[QuestoesController::class, "storage"])->name("storageQuestoes.get");

Route::post("/registrar", [LoginRegisterController::class, "registrar"]);
Route::post("/login", [LoginRegisterController::class, "login"]);


Route::middleware(['auth:sanctum'])->group(function() {
    Route::get("/temas/verificar-ou-gerar", [QuestoesController::class,"verificarTema"])->name("verificaTema.get");
    Route::get("/conjuntos/listar", [QuestoesController::class, "listaConjuntos"]);
    Route::get("/temas", [TemasController::class, "temas"]);
    Route::get("/temas/questoes", [TemasController::class, "questoes"]);
    Route::post("conjuntos/concluir", [TemasController::class, "conclusao"]);
    Route::post("/usuarios/senha",[UserController::class, "atualizaSenha"]);
    Route::post("/usuarios/avatar",[UserController::class, "atualizaAvatar"]);
    Route::post("/logout", [LoginRegisterController::class, "logout"]);
    });
    
Route::post('/forgot-password', [PasswordResetController::class, 'sendToken']);
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);

