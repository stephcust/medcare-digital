<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('historico_clinico')) {
            return;
        }

        $arquivoPath = !Schema::hasColumn('historico_clinico', 'arquivo_path');
        $arquivoUrl = !Schema::hasColumn('historico_clinico', 'arquivo_url');
        $origem = !Schema::hasColumn('historico_clinico', 'origem');
        $relato = !Schema::hasColumn('historico_clinico', 'relato_original');
        $observacoes = !Schema::hasColumn('historico_clinico', 'observacoes');

        Schema::table('historico_clinico', function (Blueprint $table) use (
            $arquivoPath,
            $arquivoUrl,
            $origem,
            $relato,
            $observacoes
        ) {
            if ($arquivoPath) {
                $table->text('arquivo_path')->nullable();
            }
            if ($arquivoUrl) {
                $table->text('arquivo_url')->nullable();
            }
            if ($origem) {
                $table->string('origem', 30)->default('manual');
            }
            if ($relato) {
                $table->text('relato_original')->nullable();
            }
            if ($observacoes) {
                $table->text('observacoes')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('historico_clinico')) {
            return;
        }

        foreach ([
            'arquivo_path',
            'arquivo_url',
            'origem',
            'relato_original',
            'observacoes',
        ] as $coluna) {
            if (Schema::hasColumn('historico_clinico', $coluna)) {
                Schema::table('historico_clinico', function (Blueprint $table) use ($coluna) {
                    $table->dropColumn($coluna);
                });
            }
        }
    }
};
