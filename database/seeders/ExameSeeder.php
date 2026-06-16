<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Exame;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class ExameSeeder extends Seeder
{
    public function run(): void
    {
        // // Encontra o seu usuário cadastrado localmente
        // $user = User::where('id', 3)->get();

        // if (!$user) {
        //     return;
        // }

        // Criando um arquivo fake no Storage local simulando a recepção da API
        $fakePath = 'exames_pacientes/fake_laudo.pdf';
        Storage::disk('local')->put($fakePath, 'Conteúdo binário simulado de um laudo médico oficial.');

        // 1. Exame simulado via API (Não visualizado pelo paciente ainda)
        Exame::create([
            'user_id' => 3,
            'nome' => 'Hemograma Completo',
            'tipo' => 'Sangue',
            'laboratorio' => 'CliniCenter',
            'data_realizacao' => now()->subDays(2),
            'arquivo_path' => $fakePath,
            'visualizado' => false, // IMPORTANTE: Aciona o card na Home
            'origem' => 'api'
        ]);

        // 2. Exame antigo simulado via API (Já visualizado)
        Exame::create([
            'user_id' => 3,
            'nome' => 'Raio-X de Tórax PA',
            'tipo' => 'Imagem',
            'laboratorio' => 'Imediata Diagnósticos',
            'data_realizacao' => now()->subMonths(1),
            'arquivo_path' => $fakePath,
            'visualizado' => true,
            'origem' => 'api'
        ]);
    }
}