<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginRegisterController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\TemasController;
use App\Http\Controllers\QuestoesController;
use App\Http\Controllers\ConjuntosController;

Route::post("/registrar", [LoginRegisterController::class, "registrar"]);
Route::post("/login", [LoginRegisterController::class, "login"]);
Route::post('/forgot-password', [PasswordResetController::class, 'sendToken']);
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);

Route::middleware(['auth:sanctum'])->group(function() {
    Route::post("/logout", [LoginRegisterController::class, "logout"])->name('logout');
    Route::get("/buscar-gerar-questoes-tema", [TemasController::class, 'buscarGerarQuestoesTema'])->name('buscarGerarQuestoesTema');
    Route::get("/listar-temas-user", [TemasController::class, "temasUser"])->name('listarTemasUser');
    Route::get("/listar-conjuntos-tema", [TemasController::class, "conjuntosTema"])->name('conjuntosTemaTema');
    Route::get("/listar-questoes-conjunto", [ConjuntosController::class, "questoesConjunto"])->name('questoesConjunto');
    Route::post("/concluir-conjunto", [ConjuntosController::class, "concluirConjunto"])->name('concluirConjunto');
});
    


