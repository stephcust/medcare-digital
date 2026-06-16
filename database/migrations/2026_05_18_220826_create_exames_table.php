<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exames', function (Blueprint $table) {
            $table->id();
            // Relacionamento com o usuário logado
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Dados do Exame
            $table->string('nome'); // Ex: Hemograma Completo, Raio-X do Tórax
            $table->string('tipo'); // Ex: Sangue, Imagem, Urina
            $table->string('laboratorio')->nullable(); // Ex: CliniCenter, Sabin
            $table->date('data_realizacao');

            // Controle de Arquivos (Armazenamento Seguro)
            $table->string('arquivo_path'); // Caminho do PDF/Imagem no Storage privado

            // Regra de Negócio: Controle de Notificação e Origem
            $table->boolean('visualizado')->default(false); // Para acender o alerta na Home
            $table->enum('origem', ['manual', 'api'])->default('manual'); // Identifica como o dado entrou

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exames');
    }
};
