<?php

namespace Database\Seeders;

use App\Models\Vacinacao;
use App\Models\Paciente;
use Illuminate\Database\Seeder;

class VacinacaoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Histórico simulando dados consumidos e salvos via Integração de API
        $historicoVacinas = [
            1 => [
                ['nome_vacina' => 'Gripe (Influenza)', 'numero_dose' => 'Reforço Anual', 'data_aplicacao' => '2026-04-10', 'lote' => 'INF2026', 'fabricante' => 'Sanofi Pasteur', 'data_proxima_dose' => '2027-04-10'],
                ['nome_vacina' => 'Covid-19 (Bivalente)', 'numero_dose' => 'Reforço', 'data_aplicacao' => '2025-11-18', 'lote' => 'COV8877', 'fabricante' => 'Pfizer', 'data_proxima_dose' => null],
            ],
            2 => [
                ['nome_vacina' => 'Antitetânica', 'numero_dose' => '1ª Dose', 'data_aplicacao' => '2025-01-10', 'lote' => 'AT1234', 'fabricante' => 'Butantan', 'data_proxima_dose' => '2025-03-10'],
                ['nome_vacina' => 'Antitetânica', 'numero_dose' => '2ª Dose', 'data_aplicacao' => '2025-03-12', 'lote' => 'AT5678', 'fabricante' => 'Butantan', 'data_proxima_dose' => null],
                ['nome_vacina' => 'Febre Amarela', 'numero_dose' => 'Dose Única', 'data_aplicacao' => '2024-05-20', 'lote' => 'FA9988', 'fabricante' => 'Bio-Manguinhos', 'data_proxima_dose' => null],
            ],
            3 => [
                ['nome_vacina' => 'Hepatite B', 'numero_dose' => '1ª Dose', 'data_aplicacao' => '2025-02-15', 'lote' => 'HB4411', 'fabricante' => 'Merck', 'data_proxima_dose' => '2025-03-15'],
                ['nome_vacina' => 'Tríplice Viral', 'numero_dose' => '1ª Dose', 'data_aplicacao' => '2025-04-01', 'lote' => 'TV3322', 'fabricante' => 'Fiocruz', 'data_proxima_dose' => null],
            ],
            // 7 => [
            //     ['nome_vacina' => 'Hepatite B', 'numero_dose' => '1ª Dose', 'data_aplicacao' => '2026-01-20', 'lote' => 'HB4411', 'fabricante' => 'Merck', 'data_proxima_dose' => '2026-02-20'],
            //     ['nome_vacina' => 'Hepatite B', 'numero_dose' => '2ª Dose', 'data_aplicacao' => '2026-02-22', 'lote' => 'HB4412', 'fabricante' => 'Merck', 'data_proxima_dose' => '2026-08-22'],
            // ]
        ];

        foreach ($historicoVacinas as $pacienteId => $vacinas) {
            if (Paciente::where('id', $pacienteId)->exists()) {
                foreach ($vacinas as $dadosVacina) {
                    Vacinacao::firstOrCreate([
                        'paciente_id' => $pacienteId,
                        'nome_vacina' => $dadosVacina['nome_vacina'],
                        'numero_dose' => $dadosVacina['numero_dose']
                    ], [
                        'data_aplicacao' => $dadosVacina['data_aplicacao'],
                        'lote' => $dadosVacina['lote'],
                        'fabricante' => $dadosVacina['fabricante'],
                        'data_proxima_dose' => $dadosVacina['data_proxima_dose'],
                    ]);
                }
            }
        }
    }
}
