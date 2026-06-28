<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('perfis_pacientes')) {
            Schema::table('perfis_pacientes', function (Blueprint $table) {
                if (!Schema::hasColumn('perfis_pacientes', 'peso_kg')) {
                    $table->decimal('peso_kg', 6, 2)->nullable();
                }
                if (!Schema::hasColumn('perfis_pacientes', 'altura_cm')) {
                    $table->unsignedSmallInteger('altura_cm')->nullable();
                }
                if (!Schema::hasColumn('perfis_pacientes', 'peso_atualizado_em')) {
                    $table->timestamp('peso_atualizado_em')->nullable();
                }
                if (!Schema::hasColumn('perfis_pacientes', 'condicoes_cronicas')) {
                    $table->json('condicoes_cronicas')->nullable();
                }
                if (!Schema::hasColumn('perfis_pacientes', 'medicamentos_continuos')) {
                    $table->json('medicamentos_continuos')->nullable();
                }
                if (!Schema::hasColumn('perfis_pacientes', 'cirurgias_anteriores')) {
                    $table->json('cirurgias_anteriores')->nullable();
                }
                if (!Schema::hasColumn('perfis_pacientes', 'dispositivos_implantes')) {
                    $table->json('dispositivos_implantes')->nullable();
                }
                if (!Schema::hasColumn('perfis_pacientes', 'observacoes_importantes')) {
                    $table->text('observacoes_importantes')->nullable();
                }
                if (!Schema::hasColumn('perfis_pacientes', 'contato_emergencia_nome')) {
                    $table->string('contato_emergencia_nome')->nullable();
                }
                if (!Schema::hasColumn('perfis_pacientes', 'contato_emergencia_telefone')) {
                    $table->string('contato_emergencia_telefone', 30)->nullable();
                }
                if (!Schema::hasColumn('perfis_pacientes', 'contato_emergencia_parentesco')) {
                    $table->string('contato_emergencia_parentesco', 80)->nullable();
                }
            });
        }

        if (
            Schema::hasTable('conversas_assistente')
            && Schema::hasTable('resumos_jornada')
            && !Schema::hasColumn('conversas_assistente', 'resumo_jornada_id')
        ) {
            Schema::table('conversas_assistente', function (Blueprint $table) {
                $table->foreignId('resumo_jornada_id')
                    ->nullable()
                    ->constrained('resumos_jornada')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (
            Schema::hasTable('conversas_assistente')
            && Schema::hasColumn('conversas_assistente', 'resumo_jornada_id')
        ) {
            Schema::table('conversas_assistente', function (Blueprint $table) {
                $table->dropConstrainedForeignId('resumo_jornada_id');
            });
        }

        if (Schema::hasTable('perfis_pacientes')) {
            $colunas = [
                'peso_kg',
                'altura_cm',
                'peso_atualizado_em',
                'condicoes_cronicas',
                'medicamentos_continuos',
                'cirurgias_anteriores',
                'dispositivos_implantes',
                'observacoes_importantes',
                'contato_emergencia_nome',
                'contato_emergencia_telefone',
                'contato_emergencia_parentesco',
            ];

            foreach ($colunas as $coluna) {
                if (Schema::hasColumn('perfis_pacientes', $coluna)) {
                    Schema::table('perfis_pacientes', function (Blueprint $table) use ($coluna) {
                        $table->dropColumn($coluna);
                    });
                }
            }
        }
    }
};
