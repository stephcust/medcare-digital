<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('conversas_assistente', 'receita_id')) {
            Schema::table(
                'conversas_assistente',
                function (Blueprint $table) {
                    $table->foreignId('receita_id')
                        ->nullable()
                        ->constrained('receitas')
                        ->nullOnDelete();
                }
            );
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('conversas_assistente', 'receita_id')) {
            Schema::table(
                'conversas_assistente',
                function (Blueprint $table) {
                    $table->dropConstrainedForeignId('receita_id');
                }
            );
        }
    }
};
