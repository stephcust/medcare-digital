<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {

        // Receitas Médicas
        Schema::create('receitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('medico');
            $table->text('medicamentos');
            $table->string('caminho_arquivo')->nullable();
            $table->date('data_emissao');
            $table->timestamps();
        });

        // Internações e Pronto-Socorro
        Schema::create('historico_clinico', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('tipo', ['Pronto-Socorro', 'Internacao']);
            $table->string('instituicao');
            $table->date('data_entrada');
            $table->date('data_saida')->nullable();
            $table->text('resumo_diagnostico')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historico_clinico');
        Schema::dropIfExists('vacinas');
        Schema::dropIfExists('receitas');
        Schema::dropIfExists('exames');
    }
};