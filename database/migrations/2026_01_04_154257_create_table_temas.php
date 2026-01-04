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
        Schema::create('temas', function (Blueprint $table) {
            $table->id();
            $table->string("nome");
            $table->foreignId("conjunto1_id")->constrained("conjunto_questoes");
            $table->foreignId("conjunto2_id")->constrained("conjunto_questoes");
            $table->foreignId("conjunto3_id")->constrained("conjunto_questoes");
            $table->foreignId("conjunto4_id")->constrained("conjunto_questoes");
            $table->foreignId("conjunto5_id")->constrained("conjunto_questoes");
            $table->foreignId("conjunto6_id")->constrained("conjunto_questoes");
            $table->foreignId("conjunto7_id")->constrained("conjunto_questoes");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temas');
    }
};
