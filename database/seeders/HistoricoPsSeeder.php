<?php

namespace Database\Seeders;

use App\Models\HistoricoClinico;
use Illuminate\Database\Seeder;
use App\Models\Paciente;
use App\Models\Patient;

class HistoricoPsSeeder extends Seeder
{
    public function run(): void
    {
        HistoricoClinico::truncate();

        // Busca o primeiro paciente cadastrado na base para vincular os prontuários
        $paciente = Paciente::first();

        if (!$paciente) {
            $this->command->error("Por favor, cadastre um Paciente antes de rodar este Seeder!");
            return;
        }

        // Caso 1: Alta Gravidade (Dor torácica)
        HistoricoClinico::create([
            'paciente_id' => $paciente->id,
            'motivo_atendimento' => 'Dor torácica aguda',
            'gravidade' => 'Alta Gravidade',
            'data_atendimento' => '2026-04-18 14:30:00',
            'local_atendimento' => 'Hospital Santa Casa - Pronto Socorro',
            'medico_nome' => 'Dr. Roberto Alves',
            'diagnostico' => 'Angina instável - descartado infarto',
            'tratamento' => 'Observação por 6 horas, ECG seriado, exames laboratoriais',
            'exames_realizados' => ['ECG', 'Troponina', 'CK-MB', 'Raio-X de tórax'],
            'medicamentos' => [
                ['nome' => 'Aspirina', 'dosagem' => '100mg'],
                ['nome' => 'Clopidogrel', 'dosagem' => '75mg'],
                ['nome' => 'Atorvastatina', 'dosagem' => '40mg']
            ],
            'desfecho' => 'Alta após observação',
            'acompanhamento' => 'Consulta com cardiologista em 7 days'
        ]);

        // Caso 2: Média Gravidade (Febre alta e tosse)
        HistoricoClinico::create([
            'paciente_id' => $paciente->id,
            'motivo_atendimento' => 'Febre alta e tosse',
            'gravidade' => 'Média Gravidade',
            'data_atendimento' => '2026-02-05 22:15:00',
            'local_atendimento' => 'UPA 24h - Vila Mariana',
            'medico_nome' => 'Dra. Fernanda Costa',
            'diagnostico' => 'Pneumonia bacteriana',
            'tratamento' => 'Hidratação venosa, antibioticoterapia',
            'exames_realizados' => ['Raio-X de tórax', 'Hemograma completo'],
            'medicamentos' => [
                ['nome' => 'Amoxicilina + Clavulanato', 'dosagem' => '875mg'],
                ['nome' => 'Dipirona', 'dosagem' => '500mg']
            ],
            'desfecho' => 'Alta com prescrição',
            'acompanhamento' => 'Retornar se persistir febre após 3 dias'
        ]);

        // Caso 3: Média Gravidade (Queda com trauma)
        HistoricoClinico::create([
            'paciente_id' => $paciente->id,
            'motivo_atendimento' => 'Queda com trauma na perna',
            'gravidade' => 'Média Gravidade',
            'data_atendimento' => '2025-12-20 16:45:00',
            'local_atendimento' => 'Hospital São Lucas - Emergência',
            'medico_nome' => 'Dr. Carlos Mendes',
            'diagnostico' => 'Fratura simples de tíbia',
            'tratamento' => 'Imobilização com tala gessada, analgesia',
            'exames_realizados' => ['Raio-X de perna (AP e perfil)'],
            'medicamentos' => [
                ['nome' => 'Ibuprofeno', 'dosagem' => '600mg'],
                ['nome' => 'Paracetamol', 'dosagem' => '750mg']
            ],
            'desfecho' => 'Alta com imobilização e agendamento de retorno',
            'acompanhamento' => 'Retorno à ortopedia em 15 dias para reavaliação'
        ]);
    }
}
