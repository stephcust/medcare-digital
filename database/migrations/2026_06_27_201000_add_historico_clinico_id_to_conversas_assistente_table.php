<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            Schema::hasTable('conversas_assistente')
            && Schema::hasTable('historico_clinico')
            && !Schema::hasColumn(
                'conversas_assistente',
                'historico_clinico_id'
            )
        ) {
            Schema::table('conversas_assistente', function (Blueprint $table) {
                $table->foreignId('historico_clinico_id')
                    ->nullable()
                    ->constrained('historico_clinico')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (
            Schema::hasTable('conversas_assistente')
            && Schema::hasColumn(
                'conversas_assistente',
                'historico_clinico_id'
            )
        ) {
            Schema::table('conversas_assistente', function (Blueprint $table) {
                $table->dropConstrainedForeignId('historico_clinico_id');
            });
        }
    }
};
