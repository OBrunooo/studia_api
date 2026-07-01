<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('modelos')->updateOrInsert(
            ['id' => 1],
            ['nome' => 'default']
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('modelos')->where('id', 1)->where('nome', 'default')->delete();
    }
};
