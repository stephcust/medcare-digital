<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversas_assistente', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('canal', 30)
                ->default('simulador');

            $table->string('autor', 20);
            $table->text('texto');
            $table->string('arquivo_nome')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'canal', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversas_assistente');
    }
};
