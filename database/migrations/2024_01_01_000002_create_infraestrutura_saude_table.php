<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Perfil do Paciente
        Schema::create('perfis_pacientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('data_nascimento');
            $table->string('tipo_sanguineo', 3)->nullable();
            $table->text('alergias_conhecidas')->nullable();
            $table->string('contato_emergencia')->nullable();
            $table->timestamps();
        });

        // Planos de Saúde
        Schema::create('planos_saude', function (Blueprint $table) {
            $table->id();
            $table->string('nome'); // Ex: Unimed, Bradesco
            $table->string('codigo_operadora')->nullable();
            $table->timestamps();
        });

        // Clínicas
        Schema::create('clinicas', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('endereco');
            $table->string('telefone')->nullable();
            $table->timestamps();
        });

        // Relacionamento Clínica x Plano (Guia Médico)
        Schema::create('clinica_plano', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinica_id')->constrained('clinicas')->onDelete('cascade');
            $table->foreignId('plano_id')->constrained('planos_saude')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinica_plano');
        Schema::dropIfExists('clinicas');
        Schema::dropIfExists('planos_saude');
        Schema::dropIfExists('perfis_pacientes');
    }
};
