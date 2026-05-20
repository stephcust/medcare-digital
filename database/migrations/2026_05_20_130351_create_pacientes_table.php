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
            $table->string('nome_completo');
            $table->date('data_nascimento');
            $table->string('cpf', 14)->unique()->nullable();
            $table->string('rg', 20)->nullable();
            $table->enum('genero', ['Masculino', 'Feminino', 'Outro'])->nullable();
            $table->string('telefone', 20)->nullable();
            $table->string('email')->unique()->nullable();

            // Dados de Endereço (Opcionais)
            $table->string('cep', 9)->nullable();
            $table->string('endereco')->nullable();
            $table->string('cidade')->nullable();
            $table->string('estado', 2)->nullable();

            // Informações Clínicas Básicas
            $table->string('tipo_sanguineo', 3)->nullable(); // Ex: A+, O-
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
