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
        Schema::create('user_conclusao_conjunto', function (Blueprint $table) {
            $table->id();
            $table->foreignId("conjunto_id")->contrained("conjunto_questoes");
            $table->foreignId("user_id")->contrained("users");
            $table->boolean("conclusao")->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_conclusao_conjunto');
    }
};
