<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Medico;
use App\Models\Clinica;

class GuiaMedicoSeeder extends Seeder
{
    public function run(): void
    {
        // Limpar registros anteriores para não duplicar
        Medico::truncate();
        Clinica::truncate();

        // Alimentando Médicos conforme o protótipo
        Medico::create([
            'nome' => 'Dra. Maria Santos',
            'especialidade' => 'Cardiologia',
            'status' => 'Disponível',
            'avaliacao' => 4.8,
            'distancia' => '1.2 km',
            'telefone' => '(92) 3456-7890'
        ]);

        Medico::create([
            'nome' => 'Dr. Carlos Oliveira',
            'especialidade' => 'Ortopedia',
            'status' => 'Emergência',
            'avaliacao' => 4.9,
            'distancia' => '2.5 km',
            'telefone' => '(92) 3456-7891'
        ]);

        Medico::create([
            'nome' => 'Dra. Ana Costa',
            'especialidade' => 'Pediatria',
            'status' => 'Disponível',
            'avaliacao' => 4.7,
            'distancia' => '3.1 km',
            'telefone' => '(92) 3456-7892'
        ]);

        // Alimentando Clínicas conforme o protótipo
        Clinica::create([
            'nome' => 'Clínica Vida & Saúde',
            'tipo' => 'Clínica Geral',
            'avaliacao' => 4.7,
            'distancia' => '1.5 km',
            'telefone' => '(92) 3789-0001',
            'servicos' => $this->prepareForPostgres(['Consultas', 'Exames Laboratoriais', 'Raio-X', 'Ultrassom'])
        ]);

        Clinica::create([
            'nome' => 'Centro Médico Excellence',
            'tipo' => 'Centro Médico',
            'avaliacao' => 4.9,
            'distancia' => '2.3 km',
            'telefone' => '(92) 3789-0002',
            'servicos' => $this->prepareForPostgres(['Cardiologia', 'Ortopedia', 'Neurologia', 'Ressonância'])
        ]);
    }

    // Auxiliar para transformar ['A', 'B'] em '{"A","B"}'
    private function prepareForPostgres(array $array): string
    {
        return '{' . implode(',', array_map(function ($item) {
            return '"' . str_replace('"', '\\"', $item) . '"';
        }, $array)) . '}';
    }
}
