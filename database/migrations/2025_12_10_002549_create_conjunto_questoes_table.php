<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('conjunto_questoes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId("user_id")->constrained("users");
            $table->foreignId("questao1_id")->constrained("questoes");
            $table->foreignId("questao2_id")->constrained("questoes");
            $table->foreignId("questao3_id")->constrained("questoes");
            $table->foreignId("questao4_id")->constrained("questoes");
            $table->foreignId("questao5_id")->constrained("questoes");
            $table->foreignId("questao6_id")->constrained("questoes");
            $table->foreignId("questao7_id")->constrained("questoes");
            $table->enum("status", ["concluido", "pendente"])->default("pendente");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conjunto_questoes');
    }
};
