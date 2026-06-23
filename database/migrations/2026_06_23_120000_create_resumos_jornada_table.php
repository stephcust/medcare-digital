<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resumos_jornada', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('titulo', 150)
                ->default('Sumário de Preparação Clínica');
            $table->string('periodo', 20)->default('todos');
            $table->json('secoes');
            $table->boolean('incluir_perguntas')->default(true);
            $table->json('conteudo');
            $table->string('origem', 20)->default('jornada');
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resumos_jornada');
    }
};
