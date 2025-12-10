<?php
use App\Http\Controllers\QuestoesController;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->group(function () {
    Route::get("/verificarTema", [QuestoesController::class,"verificarTema"])->name("verificaTema.get");
    Route::get("/gerarQuestoes", [QuestoesController::class,"gerarQuestoes"])->name("gerarQuestoes.get");
    Route::get("/verificarQuestoes",[QuestoesController::class, "verificarQuestoes"])->name("verificarQuestoes.get");
    Route::get("/verificarConjunto",[QuestoesController::class, "verificarConjunto"])->name("verificarConjunto.get");
    Route::get("/storageQuestoes",[QuestoesController::class, "storage"])->name("storageQuestoes.get");

});
