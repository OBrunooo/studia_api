<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginRegisterController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\TemasController;
use App\Http\Controllers\ConjuntosController;
use App\Http\Controllers\UserController;

Route::post("/registrar", [LoginRegisterController::class, "registrar"]);
Route::post("/login", [LoginRegisterController::class, "login"]);
Route::post('/forgot-password', [PasswordResetController::class, 'sendToken']);
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);

Route::middleware(['auth:sanctum'])->group(function() {
    Route::post("/logout", [LoginRegisterController::class, "logout"])->name('logout');
    Route::get("/buscar-gerar-tema", [TemasController::class, 'buscarGerarTema'])->name('buscarGerarTema');
    Route::get("/listar-temas-user", [TemasController::class, "temasUser"])->name('listarTemasUser');
    Route::get("/listar-conjuntos-tema", [TemasController::class, "conjuntosTema"])->name('conjuntosTemaTema');
    Route::get("/listar-questoes-conjunto", [ConjuntosController::class, "questoesConjunto"])->name('questoesConjunto');
    Route::post("/concluir-conjunto", [ConjuntosController::class, "concluirConjunto"])->name('concluirConjunto');
    Route::get("/me", [UserController::class, "userInfo"])->name('userInfo');
    Route::post("/update-avatar-user", [UserController::class, "updateAvatarUser"])->name('updateAvatarUser');
    Route::post("/update-name-user", [UserController::class, "updateNameUser"])->name('updateNameUser');
    Route::get("/listar-notificacoes-user", [UserController::class, "listarNotificacoesUser"])->name('listarNotificacoesUser');
});
    


