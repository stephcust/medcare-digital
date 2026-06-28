<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('conversas_assistente', 'exame_id')) {
            Schema::table('conversas_assistente', function (Blueprint $table) {
                $table->foreignId('exame_id')
                    ->nullable()
                    ->constrained('exames')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('conversas_assistente', 'exame_id')) {
            Schema::table('conversas_assistente', function (Blueprint $table) {
                $table->dropConstrainedForeignId('exame_id');
            });
        }
    }
};
