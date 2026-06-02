<?php

namespace Database\Seeders;

use App\Models\Paciente;
use Illuminate\Database\Seeder;
use App\Models\Plano;
use App\Models\PacientePlano;
use Illuminate\Support\Facades\DB;

class PlanoSeeder extends Seeder
{
    public function run(): void
    {
        Plano::truncate();
        PacientePlano::truncate();
        DB::table('faturas_plano')->truncate();

        $paciente = Paciente::first();
        if (!$paciente) return;

        $plano = Plano::create([
            'nome' => 'Saúde Premium Plus',
            'operadora' => 'MediCare Saúde',
            'registro_ans' => '123456',
            'acomodacao' => 'Enfermaria',
            'coberturas' => [
                ['texto' => 'Consultas médicas ilimitadas', 'incluido' => true, 'detalhe' => null],
                ['texto' => 'Pronto atendimento 24h', 'incluido' => true, 'detalhe' => null],
                ['texto' => 'Exames laboratoriais', 'incluido' => true, 'detalhe' => null],
                ['texto' => 'Internações hospitalares', 'incluido' => true, 'detalhe' => 'Enfermaria'],
                ['texto' => 'Cirurgias', 'incluido' => true, 'detalhe' => null],
                ['texto' => 'Quarto particular', 'incluido' => false, 'detalhe' => 'Disponível em plano superior'],
                ['texto' => 'Obstetrícia', 'incluido' => true, 'detalhe' => null],
                ['texto' => 'Odontologia', 'incluido' => true, 'detalhe' => null],
                ['texto' => 'Fisioterapia', 'incluido' => true, 'detalhe' => 'Até 12 sessões/ano'],
                ['texto' => 'Psicoterapia', 'incluido' => true, 'detalhe' => 'Até 24 sessões/ano'],
                ['texto' => 'Medicamentos ambulatoriais', 'incluido' => false, 'detalhe' => null],
                ['texto' => 'Reembolso', 'incluido' => true, 'detalhe' => 'Até 80% da tabela'],
            ]
        ]);

        // Vinculando o plano ao paciente com o histórico de consumo de 2026
        PacientePlano::create([
            'paciente_id' => $paciente->id,
            'plano_id' => $plano->id,
            'numero_carteirinha' => '1234 5678 9012 3456',
            'vigencia' => 'Indeterminado',
            'inicio_plano' => '2020-01-01',
            'utilizacao_atual' => [
                ['item' => 'Consultas Médicas', 'usado' => 8, 'limite' => 'Ilimitado', 'porcentagem' => 45],
                ['item' => 'Exames', 'usado' => 12, 'limite' => 'Ilimitado', 'porcentagem' => 30],
                ['item' => 'Sessões de Psicoterapia', 'usado' => 6, 'limite' => 24, 'porcentagem' => 25],
                ['item' => 'Sessões de Fisioterapia', 'usado' => 3, 'limite' => 12, 'porcentagem' => 25],
            ]
        ]);

        // Inserindo o histórico de faturas mensais
        DB::table('faturas_plano')->insert([
            ['paciente_id' => $paciente->id, 'mes_referencia' => 'Maio/2026', 'data_vencimento' => '2026-05-10', 'valor' => 489.90, 'status' => 'Pendente'],
            ['paciente_id' => $paciente->id, 'mes_referencia' => 'Abril/2026', 'data_vencimento' => '2026-04-10', 'valor' => 489.90, 'status' => 'Pago'],
            ['paciente_id' => $paciente->id, 'mes_referencia' => 'Março/2026', 'data_vencimento' => '2026-03-10', 'valor' => 489.90, 'status' => 'Pago'],
            ['paciente_id' => $paciente->id, 'mes_referencia' => 'Fevereiro/2026', 'data_vencimento' => '2026-02-10', 'valor' => 489.90, 'status' => 'Pago'],
        ]);
    }
}