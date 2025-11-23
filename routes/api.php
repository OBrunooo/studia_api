<?php
use App\Http\Controllers\GerarQuestoesController;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->group(function () {
    Route::get('/gerarPerguntas', [GerarQuestoesController::class,"gerarPerguntas"]);
});
