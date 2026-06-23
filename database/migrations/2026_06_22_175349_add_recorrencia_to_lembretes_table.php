<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lembretes', function (Blueprint $table) {
            // Agrupa todos os lembretes de um mesmo tratamento.
            $table->uuid('serie_id')
                ->nullable()
                ->index();

            // Informa se o lembrete pertence a uma recorrência.
            $table->boolean('recorrente')
                ->default(false);

            // Usado em situações como "de 8 em 8 horas".
            $table->unsignedInteger('intervalo_horas')
                ->nullable();

            // Início e término do tratamento ou acompanhamento.
            $table->dateTime('data_inicio')
                ->nullable();

            $table->dateTime('data_fim')
                ->nullable();

            // Exemplos: segunda, quarta e sexta.
            $table->json('dias_semana')
                ->nullable();

            // Permite pausar ou cancelar a série.
            $table->boolean('ativo')
                ->default(true);

            // Registra quando o aviso foi enviado.
            $table->dateTime('enviado_em')
                ->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('lembretes', function (Blueprint $table) {
            $table->dropColumn([
                'serie_id',
                'recorrente',
                'intervalo_horas',
                'data_inicio',
                'data_fim',
                'dias_semana',
                'ativo',
                'enviado_em',
            ]);
        });
    }
};