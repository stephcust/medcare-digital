<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vacinacoes', function (Blueprint $table) {
            $table->id();
            // Relacionamento com o paciente (se a tabela for 'patients', manter o padrão do banco)
            $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('cascade');
            $table->string('nome_vacina');
            $table->string('fabricante')->nullable();
            $table->string('lote')->nullable();
            $table->string('numero_dose')->default('1ª Dose'); // 1ª Dose, 2ª Dose, Dose Única, Reforço
            $table->date('data_aplicacao');
            $table->date('data_proxima_dose')->nullable(); // Aprazamento
            $table->text('observacoes')->nullable(); // Reações adversas ou notas clínicas
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vacinacoes');
    }
};
