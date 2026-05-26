<?php

namespace Database\Seeders;

use App\Models\Receita;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReceitaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $historicoReceitas = [
            3 => [
                [
                    'medico' => 'Dra. Juliana Mendes (CRM-AM 7711)',
                    'medicamentos' => "• Paracetamol 750mg\n  Tomar 1 comprimido de 6 em 6 horas se houver febre (Máx 4 dias).",
                    'data_emissao' => '2026-05-20',
                    'caminho_arquivo' => null,
                ]
            ],
            4 => [
                [
                    'medico' => 'Dr. Alexandre Medeiros (CRM-AM 4321)',
                    'medicamentos' => "• Amoxicilina + Clavulanato 875mg\n  Tomar 1 comprimido de 12 em 12 horas por 14 dias.\n\n• Ibuprofeno 600mg\n  Tomar 1 comprimido de 8 em 8 horas em caso de dor ou febre.",
                    'data_emissao' => '2026-02-10',
                    'caminho_arquivo' => null,
                ]
            ],
            5 => [
                [
                    'medico' => 'Dra. Cláudia Souza (CRM-AM 9876)',
                    'medicamentos' => "• Losartana Potássica 50mg\n  Tomar 1 comprimido ao dia pela manhã (Uso contínuo).",
                    'data_emissao' => '2026-01-20',
                    'caminho_arquivo' => null,
                ]
            ],
            6 => [
                [
                    'medico' => 'Dr. Roberto Carlos (CRM-AM 5544)',
                    'medicamentos' => "• Metformina 850mg\n  Tomar 1 comprimido após o café da manhã e 1 após o jantar.",
                    'data_emissao' => '2026-03-05',
                    'caminho_arquivo' => null,
                ]
            ],
        ];

        foreach ($historicoReceitas as $userId => $receitas) {
            // Só insere se o usuário realmente existir no banco
            if (User::where('id', $userId)->exists()) {
                foreach ($receitas as $dados) {
                    Receita::firstOrCreate([
                        'user_id' => $userId,
                        'medico' => $dados['medico'],
                        'data_emissao' => $dados['data_emissao']
                    ], [
                        'medicamentos' => $dados['medicamentos'],
                        'caminho_arquivo' => $dados['caminho_arquivo']
                    ]);
                }
            }
        }
    }
}
