<?php

namespace Database\Seeders;

use App\Models\Receita;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReceitaSeeder extends Seeder
{
    public function run(): void
    {
        // Força a existência do usuário 3 para evitar erros de chave estrangeira
        if (!User::where('id', 3)->exists()) {
            User::factory()->create(['id' => 3, 'name' => 'Stepheson Custodio']);
        }

        $receitasDesign = [
            [
                'user_id' => 3,
                'medico' => 'Dra. Maria Santos',
                'especialidade' => 'Cardiologia',
                'status' => 'Ativa',
                'data_emissao' => '2026-05-12',
                'data_validade' => '2026-11-12',
                'caminho_arquivo' => null,
                'medicamentos' => [
                    [
                        'nome' => 'Losartana',
                        'dosagem' => '50mg',
                        'frequencia' => '1x ao dia',
                        'duracao' => 'Uso contínuo'
                    ],
                    [
                        'nome' => 'Sinvastatina',
                        'dosagem' => '20mg',
                        'frequencia' => '1x ao dia (noite)',
                        'duracao' => 'Uso contínuo'
                    ]
                ]
            ],
            [
                'user_id' => 3,
                'medico' => 'Dr. Pedro Lima',
                'especialidade' => 'Dermatologia',
                'status' => 'Ativa',
                'data_emissao' => '2026-04-28',
                'data_validade' => '2026-07-28',
                'caminho_arquivo' => null,
                'medicamentos' => [
                    [
                        'nome' => 'Doxiciclina',
                        'dosagem' => '100mg',
                        'frequencia' => '2x ao dia',
                        'duracao' => '30 dias'
                    ]
                ]
            ]
        ];

        foreach ($receitasDesign as $dados) {
            Receita::updateOrCreate(
                [
                    'user_id' => $dados['user_id'],
                    'medico' => $dados['medico'],
                    'data_emissao' => $dados['data_emissao']
                ],
                [
                    'especialidade' => $dados['especialidade'],
                    'status' => $dados['status'],
                    'data_validade' => $dados['data_validade'],
                    'medicamentos' => $dados['medicamentos'],
                    'caminho_arquivo' => $dados['caminho_arquivo']
                ]
            );
        }
    }
}