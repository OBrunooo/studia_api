<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuestoesController;
use App\Http\Controllers\LoginRegisterController;
use App\Http\Controllers\TemasController;

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
    });

    Route::get("/teste", function() {
        User::create([
            "name" => "oBruno",
            "email" => "obrunogmr07@gmail.com",
            "password" => "obrunogmr07@gmail.com"
        ]);
    });

});
