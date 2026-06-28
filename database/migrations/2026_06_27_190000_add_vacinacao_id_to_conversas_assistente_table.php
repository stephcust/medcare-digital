<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('conversas_assistente', 'vacinacao_id')) {
            Schema::table('conversas_assistente', function (Blueprint $table) {
                $table->foreignId('vacinacao_id')
                    ->nullable()
                    ->constrained('vacinacoes')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('conversas_assistente', 'vacinacao_id')) {
            Schema::table('conversas_assistente', function (Blueprint $table) {
                $table->dropConstrainedForeignId('vacinacao_id');
            });
        }
    }
};
