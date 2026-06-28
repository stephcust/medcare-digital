<?php

namespace App\Services\Assistente;

use App\Models\Exame;
use App\Models\Receita;
use App\Models\User;
use App\Models\Vacinacao;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class EnvioDocumentoSimuladorService
{
    private const CONTEXTO_MINUTOS = 30;

    /**
     * @return array{
     *     texto: string,
     *     exame_id: ?int,
     *     receita_id: ?int,
     *     vacinacao_id: ?int,
     *     arquivo_nome: ?string
     * }|null
     */
    public function processar(User $user, string $mensagem): ?array
    {
        $normalizada = $this->normalizar($mensagem);

        if ($normalizada === '') {
            return null;
        }

        if ($this->ehCancelamento($normalizada)) {
            Cache::forget($this->chaveSelecao($user));
            Cache::forget($this->chaveUltimoDocumento($user));

            return $this->resposta(
                'Tudo bem. Cancelei a seleção do documento.'
            );
        }

        $selecao = Cache::get($this->chaveSelecao($user));

        if (is_array($selecao)) {
            $selecionado = $this->selecionarDoContexto(
                $user,
                $normalizada,
                $selecao
            );

            if ($selecionado !== null) {
                return $selecionado;
            }

            if (preg_match('/^\s*\d{1,2}\s*$/', $normalizada)) {
                return $this->resposta(
                    'Esse número não está na lista. Escolha uma das opções '
                    . 'mostradas ou escreva "cancelar".'
                );
            }
        }

        $tipo = $this->identificarTipo($normalizada);
        $pedidoArquivo = $this->temAcaoDeEnvio($normalizada)
            && $this->mencionaArquivoOuDocumento($normalizada);

        if ($tipo === null && $pedidoArquivo) {
            $ultimo = Cache::get($this->chaveUltimoDocumento($user));

            if (is_array($ultimo)) {
                $anexo = $this->anexarPeloContexto($user, $ultimo);

                if ($anexo !== null) {
                    return $anexo;
                }
            }

            return $this->resposta(
                'Você quer receber um exame, uma receita ou um comprovante '
                . 'de vacinação? Informe também o nome do documento, se possível.'
            );
        }

        if ($tipo === null) {
            return null;
        }

        $consulta = $this->extrairConsulta($normalizada, $tipo);
        $documentos = match ($tipo) {
            'receita' => $this->buscarReceitas($user, $consulta),
            'vacina' => $this->buscarVacinacoes($user, $consulta),
            default => $this->buscarExames($user, $consulta),
        };

        if ($documentos->count() === 1) {
            $this->salvarUltimoDocumento(
                $user,
                $tipo,
                (int) $documentos->first()->id
            );
        }

        // Quando o usuário só consulta os dados, guardamos o contexto e
        // deixamos a resposta textual para a IA.
        if (!$this->temAcaoDeEnvio($normalizada)) {
            return null;
        }

        if ($documentos->isEmpty()) {
            return $this->respostaSemResultado($user, $tipo, $consulta);
        }

        $comArquivo = $documentos
            ->filter(fn (Model $documento) => !empty($documento->arquivo_path))
            ->values();

        if ($comArquivo->isEmpty()) {
            return $this->respostaSemArquivo($tipo);
        }

        if ($comArquivo->count() === 1) {
            return $this->anexarDocumento(
                $user,
                $tipo,
                $comArquivo->first()
            );
        }

        $ids = $comArquivo->take(10)->pluck('id')->all();

        Cache::put(
            $this->chaveSelecao($user),
            [
                'tipo' => $tipo,
                'ids' => $ids,
            ],
            now()->addMinutes(self::CONTEXTO_MINUTOS)
        );

        $linhas = [
            $this->textoSelecao($tipo),
            '',
        ];

        foreach ($comArquivo->take(10) as $indice => $documento) {
            $linhas[] = ($indice + 1)
                . '. '
                . $this->descricaoDocumento($tipo, $documento);
        }

        $linhas[] = '';
        $linhas[] = 'Responda apenas com o número do documento.';

        return $this->resposta(implode("\n", $linhas));
    }

    /**
     * @return array{
     *     texto: string,
     *     exame_id: ?int,
     *     receita_id: ?int,
     *     vacinacao_id: ?int,
     *     arquivo_nome: ?string
     * }|null
     */
    private function selecionarDoContexto(
        User $user,
        string $mensagem,
        array $contexto
    ): ?array {
        if (!preg_match('/^\s*(\d{1,2})\s*$/', $mensagem, $matches)) {
            return null;
        }

        $tipo = $contexto['tipo'] ?? null;
        $ids = array_values(array_filter(
            $contexto['ids'] ?? [],
            fn ($id) => is_numeric($id)
        ));

        if (
            !in_array($tipo, ['exame', 'receita', 'vacina'], true)
            || empty($ids)
        ) {
            Cache::forget($this->chaveSelecao($user));

            return null;
        }

        $indice = (int) $matches[1] - 1;

        if (!array_key_exists($indice, $ids)) {
            return null;
        }

        $documento = $this->buscarDocumentoPorId(
            $user,
            $tipo,
            (int) $ids[$indice]
        );

        if (!$documento) {
            Cache::forget($this->chaveSelecao($user));

            return $this->resposta(
                'Esse documento não está mais disponível na sua conta.'
            );
        }

        Cache::forget($this->chaveSelecao($user));

        return $this->anexarDocumento($user, $tipo, $documento);
    }

    /**
     * @return array{
     *     texto: string,
     *     exame_id: ?int,
     *     receita_id: ?int,
     *     vacinacao_id: ?int,
     *     arquivo_nome: ?string
     * }|null
     */
    private function anexarPeloContexto(User $user, array $contexto): ?array
    {
        $tipo = $contexto['tipo'] ?? null;
        $id = isset($contexto['id']) ? (int) $contexto['id'] : 0;

        if (
            $id < 1
            || !in_array($tipo, ['exame', 'receita', 'vacina'], true)
        ) {
            return null;
        }

        $documento = $this->buscarDocumentoPorId($user, $tipo, $id);

        return $documento
            ? $this->anexarDocumento($user, $tipo, $documento)
            : null;
    }

    private function buscarDocumentoPorId(
        User $user,
        string $tipo,
        int $id
    ): ?Model {
        if ($tipo === 'receita') {
            return Receita::query()
                ->where('user_id', $user->id)
                ->whereKey($id)
                ->first();
        }

        if ($tipo === 'vacina') {
            return Vacinacao::query()
                ->whereHas(
                    'paciente',
                    fn ($query) => $query->where('user_id', $user->id)
                )
                ->whereKey($id)
                ->first();
        }

        return Exame::query()
            ->where('user_id', $user->id)
            ->whereKey($id)
            ->first();
    }

    private function buscarExames(User $user, string $consulta): Collection
    {
        $documentos = Exame::query()
            ->where('user_id', $user->id)
            ->orderByDesc('data_realizacao')
            ->orderByDesc('created_at')
            ->get();

        return $this->filtrarPorConsulta(
            $documentos,
            $consulta,
            fn (Exame $exame) => $exame->nome ?? ''
        );
    }

    private function buscarReceitas(User $user, string $consulta): Collection
    {
        $documentos = Receita::query()
            ->where('user_id', $user->id)
            ->orderByDesc('data_emissao')
            ->orderByDesc('created_at')
            ->get();

        return $this->filtrarPorConsulta(
            $documentos,
            $consulta,
            fn (Receita $receita) => $receita->medico ?? ''
        );
    }

    private function buscarVacinacoes(
        User $user,
        string $consulta
    ): Collection {
        $documentos = Vacinacao::query()
            ->whereHas(
                'paciente',
                fn ($query) => $query->where('user_id', $user->id)
            )
            ->orderByDesc('data_aplicacao')
            ->orderByDesc('created_at')
            ->get();

        return $this->filtrarPorConsulta(
            $documentos,
            $consulta,
            fn (Vacinacao $vacinacao) => implode(' ', array_filter([
                $vacinacao->nome_vacina,
                $vacinacao->fabricante,
                $vacinacao->numero_dose,
            ]))
        );
    }

    private function filtrarPorConsulta(
        Collection $documentos,
        string $consulta,
        callable $obterNome
    ): Collection {
        if ($consulta === '') {
            return $documentos->values();
        }

        $tokensConsulta = $this->tokensRelevantes($consulta);

        return $documentos
            ->filter(function (Model $documento) use (
                $consulta,
                $tokensConsulta,
                $obterNome
            ) {
                $nome = $this->normalizar((string) $obterNome($documento));

                if (
                    Str::contains($nome, $consulta)
                    || Str::contains($consulta, $nome)
                ) {
                    return true;
                }

                if (empty($tokensConsulta)) {
                    return false;
                }

                $tokensNome = $this->tokensRelevantes($nome);
                $comuns = array_intersect($tokensConsulta, $tokensNome);

                return count($comuns) >= 1;
            })
            ->values();
    }

    private function respostaSemResultado(
        User $user,
        string $tipo,
        string $consulta
    ): array {
        $documentos = match ($tipo) {
            'receita' => Receita::query()
                ->where('user_id', $user->id)
                ->whereNotNull('arquivo_path')
                ->orderByDesc('data_emissao')
                ->limit(5)
                ->get(),
            'vacina' => Vacinacao::query()
                ->whereHas(
                    'paciente',
                    fn ($query) => $query->where('user_id', $user->id)
                )
                ->whereNotNull('arquivo_path')
                ->orderByDesc('data_aplicacao')
                ->limit(5)
                ->get(),
            default => Exame::query()
                ->where('user_id', $user->id)
                ->whereNotNull('arquivo_path')
                ->orderByDesc('data_realizacao')
                ->limit(5)
                ->get(),
        };

        if ($documentos->isEmpty()) {
            return $this->resposta(match ($tipo) {
                'receita' => 'Não encontrei receitas com PDF anexado '
                    . 'na sua conta.',
                'vacina' => 'Não encontrei comprovantes de vacinação '
                    . 'anexados na sua conta.',
                default => 'Não encontrei exames com arquivo anexado '
                    . 'na sua conta.',
            });
        }

        $procurado = $consulta !== ''
            ? "\"{$consulta}\""
            : 'informado';

        $linhas = [
            match ($tipo) {
                'receita' => "Não encontrei uma receita relacionada a "
                    . "{$procurado}.",
                'vacina' => "Não encontrei uma vacinação relacionada a "
                    . "{$procurado}.",
                default => "Não encontrei um exame relacionado a "
                    . "{$procurado}.",
            },
            '',
            match ($tipo) {
                'receita' => 'Receitas com arquivo disponíveis:',
                'vacina' => 'Vacinações com comprovante disponíveis:',
                default => 'Exames com arquivo disponíveis:',
            },
        ];

        foreach ($documentos as $documento) {
            $linhas[] = '• ' . $this->descricaoDocumento($tipo, $documento);
        }

        $linhas[] = '';
        $linhas[] = match ($tipo) {
            'receita' => 'Peça novamente usando o nome do médico.',
            'vacina' => 'Peça novamente usando o nome da vacina.',
            default => 'Peça novamente usando o nome do exame.',
        };

        return $this->resposta(implode("\n", $linhas));
    }

    private function respostaSemArquivo(string $tipo): array
    {
        return $this->resposta(match ($tipo) {
            'receita' => 'Encontrei a receita, mas ela não possui '
                . 'um PDF anexado.',
            'vacina' => 'Encontrei a vacinação, mas ela não possui '
                . 'um comprovante anexado.',
            default => 'Encontrei o exame, mas ele não possui '
                . 'um arquivo anexado.',
        });
    }

    private function anexarDocumento(
        User $user,
        string $tipo,
        Model $documento
    ): array {
        if ($tipo === 'receita' && $documento instanceof Receita) {
            return $this->anexarReceita($user, $documento);
        }

        if ($tipo === 'vacina' && $documento instanceof Vacinacao) {
            return $this->anexarVacinacao($user, $documento);
        }

        if ($documento instanceof Exame) {
            return $this->anexarExame($user, $documento);
        }

        return $this->resposta(
            'Não foi possível preparar esse documento para envio.'
        );
    }

    private function anexarExame(User $user, Exame $exame): array
    {
        if (!$exame->arquivo_path) {
            return $this->resposta(
                "Encontrei o exame \"{$exame->nome}\", mas ele não possui "
                . 'arquivo anexado.'
            );
        }

        $this->salvarUltimoDocumento($user, 'exame', (int) $exame->id);

        $data = $exame->data_realizacao
            ? $exame->data_realizacao->format('d/m/Y')
            : 'não informada';

        return [
            'texto' => "Encontrei o seu exame.\n\n"
                . "Exame: {$exame->nome}\n"
                . "Data: {$data}\n\n"
                . 'Use os botões abaixo para visualizar ou baixar o arquivo.',
            'exame_id' => (int) $exame->id,
            'receita_id' => null,
            'vacinacao_id' => null,
            'arquivo_nome' => $this->nomeArquivoExame($exame),
        ];
    }

    private function anexarReceita(User $user, Receita $receita): array
    {
        if (!$receita->arquivo_path) {
            return $this->resposta(
                'Encontrei a receita, mas ela não possui um PDF anexado.'
            );
        }

        $this->salvarUltimoDocumento($user, 'receita', (int) $receita->id);

        $data = $receita->data_emissao
            ? $receita->data_emissao->format('d/m/Y')
            : 'não informada';

        return [
            'texto' => "Encontrei a sua prescrição.\n\n"
                . "Médico(a): {$receita->medico}\n"
                . "Data de emissão: {$data}\n\n"
                . 'Use os botões abaixo para visualizar ou baixar o PDF.',
            'exame_id' => null,
            'receita_id' => (int) $receita->id,
            'vacinacao_id' => null,
            'arquivo_nome' => $this->nomeArquivoReceita($receita),
        ];
    }

    private function anexarVacinacao(
        User $user,
        Vacinacao $vacinacao
    ): array {
        if (!$vacinacao->arquivo_path) {
            return $this->resposta(
                'Encontrei a vacinação, mas ela não possui '
                . 'um comprovante anexado.'
            );
        }

        $this->salvarUltimoDocumento(
            $user,
            'vacina',
            (int) $vacinacao->id
        );

        $data = $vacinacao->data_aplicacao
            ? $vacinacao->data_aplicacao->format('d/m/Y')
            : 'não informada';

        return [
            'texto' => "Encontrei o seu comprovante de vacinação.\n\n"
                . "Vacina: {$vacinacao->nome_vacina}\n"
                . "Dose: {$vacinacao->numero_dose}\n"
                . "Data de aplicação: {$data}\n\n"
                . 'Use os botões abaixo para visualizar ou baixar o arquivo.',
            'exame_id' => null,
            'receita_id' => null,
            'vacinacao_id' => (int) $vacinacao->id,
            'arquivo_nome' => $this->nomeArquivoVacinacao($vacinacao),
        ];
    }

    private function descricaoDocumento(
        string $tipo,
        Model $documento
    ): string {
        if ($tipo === 'receita' && $documento instanceof Receita) {
            $data = $documento->data_emissao
                ? $documento->data_emissao->format('d/m/Y')
                : 'data não informada';

            return "{$documento->medico} — {$data}";
        }

        if ($tipo === 'vacina' && $documento instanceof Vacinacao) {
            $data = $documento->data_aplicacao
                ? $documento->data_aplicacao->format('d/m/Y')
                : 'data não informada';

            return "{$documento->nome_vacina} "
                . "({$documento->numero_dose}) — {$data}";
        }

        if ($documento instanceof Exame) {
            $data = $documento->data_realizacao
                ? $documento->data_realizacao->format('d/m/Y')
                : 'data não informada';

            return "{$documento->nome} — {$data}";
        }

        return 'Documento';
    }

    private function nomeArquivoExame(Exame $exame): string
    {
        $extensao = $this->extensaoDoCaminho($exame->arquivo_path);

        return Str::slug($exame->nome ?: 'exame') . ".{$extensao}";
    }

    private function nomeArquivoReceita(Receita $receita): string
    {
        $extensao = $this->extensaoDoCaminho($receita->arquivo_path);
        $medico = Str::slug($receita->medico ?: 'medico');
        $data = $receita->data_emissao
            ? $receita->data_emissao->format('Y-m-d')
            : 'sem-data';

        return "prescricao-{$medico}-{$data}.{$extensao}";
    }

    private function nomeArquivoVacinacao(Vacinacao $vacinacao): string
    {
        $extensao = $this->extensaoDoCaminho($vacinacao->arquivo_path);
        $vacina = Str::slug($vacinacao->nome_vacina ?: 'vacina');
        $data = $vacinacao->data_aplicacao
            ? $vacinacao->data_aplicacao->format('Y-m-d')
            : 'sem-data';

        return "comprovante-{$vacina}-{$data}.{$extensao}";
    }

    private function extensaoDoCaminho(?string $caminho): string
    {
        $extensao = strtolower(
            pathinfo((string) $caminho, PATHINFO_EXTENSION)
        );

        return $extensao !== '' ? $extensao : 'pdf';
    }

    private function identificarTipo(string $mensagem): ?string
    {
        if (
            Str::contains($mensagem, [
                'vacina',
                'vacinacao',
                'imunizacao',
                'imunizante',
                'cartao de vacina',
                'caderneta de vacina',
                'comprovante de vacina',
            ])
        ) {
            return 'vacina';
        }

        if (
            Str::contains($mensagem, [
                'receita',
                'prescricao',
                'receituario',
                'medicamento prescrito',
            ])
        ) {
            return 'receita';
        }

        if (
            Str::contains($mensagem, [
                'exame',
                'laudo',
                'hemograma',
                'resultado do exame',
            ])
        ) {
            return 'exame';
        }

        return null;
    }

    private function temAcaoDeEnvio(string $mensagem): bool
    {
        return Str::contains($mensagem, [
            'envie',
            'enviar',
            'manda',
            'mande',
            'mandar',
            'receber',
            'quero receber',
            'quero baixar',
            'baixar',
            'download',
            'abra',
            'abrir',
            'mostre',
            'me passe',
            'me da',
        ]);
    }

    private function mencionaArquivoOuDocumento(string $mensagem): bool
    {
        return Str::contains($mensagem, [
            'pdf',
            'arquivo',
            'documento',
            'anexo',
            'receita',
            'prescricao',
            'exame',
            'laudo',
            'vacina',
            'vacinacao',
            'imunizacao',
            'comprovante',
            'caderneta',
            'cartao',
        ]);
    }

    private function extrairConsulta(string $mensagem, string $tipo): string
    {
        $consulta = $mensagem;

        $padroesGerais = [
            '/\b(por favor|pra mim|para mim|pelo whatsapp|no whatsapp|aqui)\b/u',
            '/\b(envie|enviar|manda|mande|mandar|receber|baixar|download|abra|abrir|mostre|passe)\b/u',
            '/\b(quero|gostaria|preciso|pode|consegue|voce|vc)\b/u',
            '/\b(me|meu|minha|o|a|um|uma|do|da|de|em|com|pelo|arquivo|documento|pdf|anexo)\b/u',
        ];

        $padroesTipo = match ($tipo) {
            'receita' => [
                '/\b(receita|prescricao|receituario)\b/u',
                '/\b(doutor|doutora|dr|dra|medico|medica)\b/u',
            ],
            'vacina' => [
                '/\b(vacina|vacinacao|imunizacao|imunizante)\b/u',
                '/\b(comprovante|cartao|caderneta|dose)\b/u',
            ],
            default => [
                '/\b(exame|laudo|resultado)\b/u',
            ],
        };

        foreach (array_merge($padroesGerais, $padroesTipo) as $padrao) {
            $consulta = preg_replace($padrao, ' ', $consulta);
        }

        $consulta = preg_replace('/[^a-z0-9\s]/u', ' ', $consulta);
        $consulta = preg_replace('/\s+/', ' ', $consulta);

        return trim($consulta);
    }

    private function tokensRelevantes(string $texto): array
    {
        $ignoradas = [
            'dr',
            'dra',
            'doutor',
            'doutora',
            'medico',
            'medica',
            'exemplo',
            'completo',
            'vacina',
            'vacinacao',
            'imunizacao',
            'dose',
            'comprovante',
        ];

        $tokens = preg_split('/\s+/', $this->normalizar($texto)) ?: [];

        return array_values(array_filter(
            array_unique($tokens),
            fn ($token) => strlen($token) >= 3
                && !in_array($token, $ignoradas, true)
        ));
    }

    private function salvarUltimoDocumento(
        User $user,
        string $tipo,
        int $id
    ): void {
        Cache::put(
            $this->chaveUltimoDocumento($user),
            [
                'tipo' => $tipo,
                'id' => $id,
            ],
            now()->addMinutes(self::CONTEXTO_MINUTOS)
        );
    }

    private function textoSelecao(string $tipo): string
    {
        return match ($tipo) {
            'receita' => 'Encontrei mais de uma receita. '
                . 'Qual delas você deseja receber?',
            'vacina' => 'Encontrei mais de uma vacinação. '
                . 'Qual comprovante você deseja receber?',
            default => 'Encontrei mais de um exame. '
                . 'Qual deles você deseja receber?',
        };
    }

    private function ehCancelamento(string $mensagem): bool
    {
        return Str::contains($mensagem, [
            'cancelar',
            'cancela',
            'deixa pra la',
            'deixe pra la',
            'nao quero mais',
        ]);
    }

    private function resposta(string $texto): array
    {
        return [
            'texto' => $texto,
            'exame_id' => null,
            'receita_id' => null,
            'vacinacao_id' => null,
            'arquivo_nome' => null,
        ];
    }

    private function chaveSelecao(User $user): string
    {
        return "medcare:simulador:selecao-documento:{$user->id}";
    }

    private function chaveUltimoDocumento(User $user): string
    {
        return "medcare:simulador:ultimo-documento:{$user->id}";
    }

    private function normalizar(string $texto): string
    {
        return Str::lower(Str::ascii(trim($texto)));
    }
}
