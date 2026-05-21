<?php

namespace Database\Seeders;

use App\Models\Paciente;
use Illuminate\Database\Seeder;

class PacienteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pacientes = [
            [
                'id' => 4,
                'nome_completo' => 'Stepheson Custodio',
                'data_nascimento' => '2004-03-02',
                'cpf' => '703.435.562-27',
                'rg' => '1234567-4',
                'genero' => 'Masculino',
                'telefone' => '(11) 98888-4444',
                'email' => 'carlos.silva@email.com',
                'cep' => '01001-000',
                'endereco' => 'Praça da Sé, 40',
                'cidade' => 'São Paulo',
                'estado' => 'SP',
                'tipo_sanguineo' => 'A+',
                'alergias_conhecidas' => 'Dipirona',
            ],
            [
                'id' => 5,
                'nome_completo' => 'Mariana Oliveira',
                'data_nascimento' => '1992-08-25',
                'cpf' => '123.456.789-05',
                'rg' => '1234567-5',
                'genero' => 'Feminino',
                'telefone' => '(11) 98888-5555',
                'email' => 'mariana.oliveira@email.com',
                'cep' => '01001-000',
                'endereco' => 'Av. Paulista, 500',
                'cidade' => 'São Paulo',
                'estado' => 'SP',
                'tipo_sanguineo' => 'O-',
                'alergias_conhecidas' => 'Nenhuma',
            ],
            [
                'id' => 6,
                'nome_completo' => 'Roberto Santos',
                'data_nascimento' => '1978-11-02',
                'cpf' => '123.456.789-06',
                'rg' => '1234567-6',
                'genero' => 'Masculino',
                'telefone' => '(21) 97777-6666',
                'email' => 'roberto.santos@email.com',
                'cep' => '22020-001',
                'endereco' => 'Av. Atlântica, 600',
                'cidade' => 'Rio de Janeiro',
                'estado' => 'RJ',
                'tipo_sanguineo' => 'B+',
                'alergias_conhecidas' => 'Penicilina',
            ],
            [
                'id' => 7,
                'nome_completo' => 'Ana Beatriz Costa',
                'data_nascimento' => '2000-01-15',
                'cpf' => '123.456.789-07',
                'rg' => '1234567-7',
                'genero' => 'Feminino',
                'telefone' => '(31) 96666-7777',
                'email' => 'ana.costa@email.com',
                'cep' => '30140-010',
                'endereco' => 'Rua da Bahia, 700',
                'cidade' => 'Belo Horizonte',
                'estado' => 'MG',
                'tipo_sanguineo' => 'AB+',
                'alergias_conhecidas' => 'Frutos do mar',
            ],
        ];

        foreach ($pacientes as $dados) {
            // O primeiro array identifica o registro pelo ID, o segundo atualiza/insere os dados
            Paciente::updateOrCreate(['id' => $dados['id']], $dados);
        }
    }
}
