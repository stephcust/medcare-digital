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
        Schema::create('pacientes', function (Blueprint $table) {
            $table->id();

            // Relacionamento com a tabela users (Chave Estrangeira Única)
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');

            // Dados específicos do paciente
            $table->string('rg')->nullable();
            $table->string('genero', 20)->nullable();
            $table->string('telefone')->nullable();
            $table->string('cep', 9)->nullable();
            $table->string('endereco')->nullable();
            $table->string('cidade')->nullable();
            $table->string('estado', 2)->nullable();
            $table->string('tipo_sanguineo', 5)->nullable();
            $table->text('alergias_conhecidas')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pacientes');
    }
};
