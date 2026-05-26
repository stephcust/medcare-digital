<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receitas', function (Blueprint $table) {
            $table->id();

            // Relacionamentos obrigatórios
            $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('cascade');
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade'); // Médico/Profissional emissor

            // Dados da Receita
            $table->string('medicamento');
            $table->string('dosagem');
            $table->string('frequencia'); // Ex: "De 8 em 8 horas"
            $table->integer('duracao_dias')->nullable(); // Ex: 7 dias
            $table->date('data_emissao');
            $table->date('data_validade')->nullable();
            $table->text('orientacoes_adicionais')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receitas');
    }
};