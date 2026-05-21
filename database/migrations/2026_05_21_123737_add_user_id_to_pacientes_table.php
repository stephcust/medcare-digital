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
        Schema::table('pacientes', function (Blueprint $table) {
            // Cria a coluna user_id como chave estrangeira única
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            // Remove a chave estrangeira e a coluna se der rollback
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
