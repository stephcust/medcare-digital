<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { router, useForm } from '@inertiajs/vue3'
import axios from 'axios'
import { ref } from 'vue'

const props = defineProps({
    relatos: {
        type: Array,
        default: () => [],
    },
    resumosSalvos: {
        type: Array,
        default: () => [],
    },
})

const tentarInterpretarJson = (valor) => {
    if (typeof valor !== 'string') return null

    const texto = valor.trim()

    if (!texto) return null

    try {
        return JSON.parse(texto)
    } catch {
        const inicio = texto.indexOf('{')
        const fim = texto.lastIndexOf('}')

        if (inicio === -1 || fim < inicio) return null

        try {
            return JSON.parse(texto.slice(inicio, fim + 1))
        } catch {
            return null
        }
    }
}

const normalizarResumo = (conteudo) => {
    let resultado = conteudo

    if (typeof resultado === 'string') {
        resultado = tentarInterpretarJson(resultado)
    }

    if (!resultado || typeof resultado !== 'object') {
        return null
    }

    const secoes = Array.isArray(resultado.secoes)
        ? resultado.secoes
        : []

    // Recupera resumos antigos que salvaram cada linha do JSON como item.
    if (
        secoes.length === 1
        && secoes[0]?.id === 'resumo'
        && Array.isArray(secoes[0]?.itens)
    ) {
        const recuperado = tentarInterpretarJson(
            secoes[0].itens.join('\n'),
        )

        if (recuperado) {
            resultado = recuperado
        }
    }

    return {
        titulo:
            resultado.titulo
            ?? 'Sumário de Preparação Clínica',
        periodo:
            resultado.periodo
            ?? 'Todo o histórico disponível',
        secoes: Array.isArray(resultado.secoes)
            ? resultado.secoes
            : [],
        perguntas_medico: Array.isArray(resultado.perguntas_medico)
            ? resultado.perguntas_medico
            : [],
    }
}

const normalizarRegistro = (registro) => ({
    ...registro,
    conteudo: normalizarResumo(registro?.conteudo),
})

const listaResumos = ref(
    props.resumosSalvos.map(normalizarRegistro),
)
const resumo = ref(listaResumos.value[0]?.conteudo ?? null)
const resumoAtualId = ref(listaResumos.value[0]?.id ?? null)
const apagandoResumoId = ref(null)
const carregandoResumo = ref(false)
const mostrarOpcoesResumo = ref(false)
const erroResumo = ref('')

const secoesDisponiveis = [
    {
        id: 'dados_pessoais',
        titulo: 'Dados pessoais',
        descricao: 'Nome e e-mail cadastrados.',
    },
    {
        id: 'relatos',
        titulo: 'Sintomas e relatos',
        descricao: 'Registros marcados para preparação de consultas.',
    },
    {
        id: 'exames',
        titulo: 'Exames',
        descricao: 'Exames e resultados cadastrados.',
    },
    {
        id: 'receitas',
        titulo: 'Receitas',
        descricao: 'Receitas e medicamentos registrados.',
    },
    {
        id: 'vacinas',
        titulo: 'Vacinas',
        descricao: 'Doses aplicadas e próximas doses.',
    },
    {
        id: 'historico_clinico',
        titulo: 'Histórico Clínico',
        descricao: 'Ocorrências e informações da trajetória de saúde.',
    },
]

const opcoesResumo = ref({
    periodo: 'todos',
    secoes: [
        'dados_pessoais',
        'relatos',
        'exames',
        'receitas',
        'vacinas',
        'historico_clinico',
    ],
    incluir_perguntas: true,
})

const form = useForm({
    categoria: 'sintoma',
    titulo: '',
    relato: '',
    data_ocorrencia: new Date().toISOString().slice(0, 10),
    incluir_no_resumo: true,
})

const salvarRelato = () => {
    form.post(route('jornada-inteligente.relatos.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('titulo', 'relato')
            form.categoria = 'sintoma'
            form.data_ocorrencia = new Date().toISOString().slice(0, 10)
            form.incluir_no_resumo = true
        },
    })
}

const alternarSecao = (secaoId) => {
    const secoes = opcoesResumo.value.secoes

    if (secoes.includes(secaoId)) {
        opcoesResumo.value.secoes = secoes.filter(
            (item) => item !== secaoId,
        )
        return
    }

    opcoesResumo.value.secoes = [...secoes, secaoId]
}

const abrirOpcoesResumo = () => {
    erroResumo.value = ''
    mostrarOpcoesResumo.value = true
}

const fecharOpcoesResumo = () => {
    if (!carregandoResumo.value) {
        mostrarOpcoesResumo.value = false
    }
}

const gerarResumo = async () => {
    if (opcoesResumo.value.secoes.length === 0) {
        erroResumo.value = 'Selecione pelo menos uma informação.'
        return
    }

    carregandoResumo.value = true
    erroResumo.value = ''
    resumo.value = null

    try {
        const response = await axios.post(
            route('jornada-inteligente.resumo'),
            {
                periodo: opcoesResumo.value.periodo,
                secoes: opcoesResumo.value.secoes,
                incluir_perguntas:
                    opcoesResumo.value.incluir_perguntas,
            },
        )

        const registro = response.data.registro
            ? normalizarRegistro(response.data.registro)
            : null

        resumo.value = normalizarResumo(response.data.resumo)
        resumoAtualId.value = registro?.id ?? null

        if (registro) {
            listaResumos.value = [
                registro,
                ...listaResumos.value.filter(
                    (item) => item.id !== registro.id,
                ),
            ]
        }

        mostrarOpcoesResumo.value = false
    } catch (error) {
        erroResumo.value =
            error.response?.data?.message
            ?? 'Não consegui compilar os dados agora. Tente novamente.'
    } finally {
        carregandoResumo.value = false
    }
}

const abrirResumoSalvo = (registro) => {
    resumo.value = normalizarResumo(registro.conteudo)
    resumoAtualId.value = registro.id

    window.scrollTo({
        top: 0,
        behavior: 'smooth',
    })
}

const apagarResumo = (resumoId) => {
    const confirmou = window.confirm(
        'Tem certeza que deseja apagar este resumo?',
    )

    if (!confirmou) return

    apagandoResumoId.value = resumoId

    router.delete(
        route('jornada-inteligente.resumos.destroy', {
            resumo: resumoId,
        }),
        {
            preserveScroll: true,
            onSuccess: () => {
                listaResumos.value = listaResumos.value.filter(
                    (item) => item.id !== resumoId,
                )

                if (resumoAtualId.value === resumoId) {
                    const proximo = listaResumos.value[0] ?? null
                    resumo.value = proximo?.conteudo ?? null
                    resumoAtualId.value = proximo?.id ?? null
                }
            },
            onError: () => {
                window.alert(
                    'Não foi possível apagar o resumo.',
                )
            },
            onFinish: () => {
                apagandoResumoId.value = null
            },
        },
    )
}

const nomeOrigem = (origem) => {
    return origem === 'simulador'
        ? 'Gerado pelo simulador'
        : 'Gerado na Jornada'
}

const formatarData = (dataString) => {
    if (!dataString) return ''

    const partesGerais = dataString.replace('T', ' ').split(' ')
    const dataPura = partesGerais[0]
    const horaPura = partesGerais[1]
        ? partesGerais[1].substring(0, 5)
        : ''

    const partesData = dataPura.split('-')

    if (partesData.length !== 3) return dataString

    const [ano, mes, dia] = partesData
    const dataFormatada = `${dia}/${mes}/${ano}`

    return horaPura
        ? `${dataFormatada} às ${horaPura}`
        : dataFormatada
}
</script>

<template>
    <AppLayout title="Jornada Inteligente">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Jornada Inteligente de Saúde
            </h2>
        </template>

        <div class="py-12 text-left">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="space-y-6">
                        <div
                            class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm"
                        >
                            <h3 class="text-base font-bold text-gray-800 mb-2">
                                Registrar Linha do Tempo
                            </h3>

                            <p class="text-xs text-gray-500 mb-4">
                                Insira sintomas, dores ou observações cotidianas
                                para enriquecer sua linha do tempo de saúde.
                            </p>

                            <form
                                class="space-y-4"
                                @submit.prevent="salvarRelato"
                            >
                                <div>
                                    <label
                                        class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wider"
                                    >
                                        Categoria
                                    </label>

                                    <select
                                        v-model="form.categoria"
                                        class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-indigo-500"
                                    >
                                        <option value="sintoma">
                                            Sintoma / Desconforto
                                        </option>
                                        <option value="reacao">
                                            Reação a medicamento
                                        </option>
                                        <option value="observacao">
                                            Observação geral
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label
                                        class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wider"
                                    >
                                        Título simples
                                    </label>

                                    <input
                                        v-model="form.titulo"
                                        type="text"
                                        placeholder="Ex.: Dor de cabeça forte"
                                        class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-indigo-500"
                                        required
                                    />
                                </div>

                                <div>
                                    <label
                                        class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wider"
                                    >
                                        Data do evento
                                    </label>

                                    <input
                                        v-model="form.data_ocorrencia"
                                        type="date"
                                        class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-indigo-500"
                                        required
                                    />
                                </div>

                                <div>
                                    <label
                                        class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wider"
                                    >
                                        Relato detalhado
                                    </label>

                                    <textarea
                                        v-model="form.relato"
                                        placeholder="Descreva o que sentiu, a intensidade e quanto tempo durou..."
                                        class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm h-28 resize-none focus:outline-none focus:border-indigo-500"
                                        required
                                    ></textarea>
                                </div>

                                <div class="flex items-center gap-2 py-1">
                                    <input
                                        id="incluir"
                                        v-model="form.incluir_no_resumo"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                    />

                                    <label
                                        for="incluir"
                                        class="text-xs text-gray-600 font-medium select-none cursor-pointer"
                                    >
                                        Usar este relato na preparação de
                                        consultas
                                    </label>
                                </div>

                                <button
                                    type="submit"
                                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 rounded-xl text-xs transition border-none cursor-pointer shadow-xs disabled:opacity-60"
                                    :disabled="form.processing"
                                >
                                    Salvar na Jornada
                                </button>
                            </form>
                        </div>

                        <div
                            class="bg-gradient-to-br from-indigo-900 to-slate-900 p-6 rounded-3xl text-white shadow-sm"
                        >
                            <h4 class="text-sm font-black mb-1">
                                Vai ao médico?
                            </h4>

                            <p
                                class="text-xs text-indigo-200 mb-4 leading-relaxed"
                            >
                                Escolha o período e as informações que deseja
                                incluir em um sumário para a consulta.
                            </p>

                            <button
                                class="w-full bg-white text-indigo-900 hover:bg-indigo-50 font-bold py-3 rounded-xl text-xs transition border-none cursor-pointer flex items-center justify-center gap-2 shadow-xs"
                                type="button"
                                @click="abrirOpcoesResumo"
                            >
                                <i class="pi pi-sliders-h"></i>
                                Escolher e gerar sumário
                            </button>
                        </div>
                    </div>

                    <div class="lg:col-span-2 space-y-6">
                        <div
                            v-if="resumo"
                            class="bg-indigo-50 border border-indigo-100 rounded-3xl p-6 shadow-xs"
                        >
                            <div
                                class="flex justify-between items-start border-b border-indigo-100 pb-3 mb-5"
                            >
                                <div>
                                    <span
                                        class="bg-indigo-600 text-white font-extrabold text-[9px] px-2 py-0.5 rounded-sm uppercase tracking-wider"
                                    >
                                        Documento auxiliar
                                    </span>

                                    <h3
                                        class="text-lg font-black text-indigo-950 mt-1"
                                    >
                                        {{ resumo.titulo }}
                                    </h3>

                                    <p class="text-xs text-indigo-500 mt-1">
                                        {{ resumo.periodo }}
                                    </p>
                                </div>

                                <div class="flex items-center gap-2">
                                    <i
                                        class="pi pi-sparkles text-indigo-600 text-lg"
                                    ></i>

                                    <button
                                        v-if="resumoAtualId"
                                        type="button"
                                        class="w-9 h-9 rounded-xl bg-white text-rose-600 border border-rose-100 hover:bg-rose-50 cursor-pointer disabled:opacity-60"
                                        :disabled="apagandoResumoId === resumoAtualId"
                                        title="Apagar resumo"
                                        @click="apagarResumo(resumoAtualId)"
                                    >
                                        <i
                                            :class="
                                                apagandoResumoId === resumoAtualId
                                                    ? 'pi pi-spin pi-spinner'
                                                    : 'pi pi-trash'
                                            "
                                        ></i>
                                    </button>
                                </div>
                            </div>

                            <div class="space-y-5">
                                <section
                                    v-for="secao in resumo.secoes"
                                    :key="secao.id"
                                    class="bg-white/70 border border-indigo-100 rounded-2xl p-4"
                                >
                                    <h4
                                        class="text-sm font-extrabold text-indigo-950 mb-2"
                                    >
                                        {{ secao.titulo }}
                                    </h4>

                                    <ul
                                        class="space-y-2 text-sm text-indigo-900"
                                    >
                                        <li
                                            v-for="(item, indice) in secao.itens"
                                            :key="`${secao.id}-${indice}`"
                                            class="flex gap-2 leading-relaxed"
                                        >
                                            <span
                                                class="text-indigo-500 font-bold"
                                            >
                                                •
                                            </span>
                                            <span>{{ item }}</span>
                                        </li>
                                    </ul>
                                </section>

                                <section
                                    v-if="resumo.perguntas_medico?.length"
                                    class="bg-white/70 border border-indigo-100 rounded-2xl p-4"
                                >
                                    <h4
                                        class="text-sm font-extrabold text-indigo-950 mb-2"
                                    >
                                        Perguntas para a consulta
                                    </h4>

                                    <ol
                                        class="space-y-2 text-sm text-indigo-900"
                                    >
                                        <li
                                            v-for="(pergunta, indice) in resumo.perguntas_medico"
                                            :key="`pergunta-${indice}`"
                                            class="flex gap-2 leading-relaxed"
                                        >
                                            <span
                                                class="text-indigo-600 font-bold"
                                            >
                                                {{ indice + 1 }}.
                                            </span>
                                            <span>{{ pergunta }}</span>
                                        </li>
                                    </ol>
                                </section>
                            </div>

                            <p
                                class="text-[11px] text-indigo-400 mt-5 italic"
                            >
                                Este documento organiza os dados selecionados
                                para apoiar a conversa com o profissional de
                                saúde. Ele não substitui avaliação médica.
                            </p>
                        </div>

                        <div
                            class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm"
                        >
                            <div
                                class="flex items-center justify-between gap-4 mb-4"
                            >
                                <div>
                                    <h3
                                        class="text-base font-bold text-gray-800"
                                    >
                                        Resumos salvos
                                    </h3>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Os resumos continuam disponíveis quando
                                        você sair e voltar ao módulo.
                                    </p>
                                </div>

                                <span
                                    class="text-xs font-bold text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full"
                                >
                                    {{ listaResumos.length }}
                                </span>
                            </div>

                            <div
                                v-if="listaResumos.length === 0"
                                class="text-center py-8 text-gray-400 text-sm"
                            >
                                Nenhum resumo foi salvo ainda.
                            </div>

                            <div v-else class="space-y-3">
                                <div
                                    v-for="registro in listaResumos"
                                    :key="registro.id"
                                    class="p-4 rounded-2xl border transition"
                                    :class="
                                        resumoAtualId === registro.id
                                            ? 'bg-indigo-50 border-indigo-200'
                                            : 'bg-gray-50/60 border-gray-100'
                                    "
                                >
                                    <div
                                        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
                                    >
                                        <div class="min-w-0">
                                            <h4
                                                class="text-sm font-bold text-gray-800 truncate"
                                            >
                                                {{ registro.titulo }}
                                            </h4>
                                            <p
                                                class="text-xs text-gray-500 mt-1"
                                            >
                                                {{ registro.periodo }} ·
                                                {{ registro.criado_em }}
                                            </p>
                                            <p
                                                class="text-[11px] text-indigo-500 mt-1"
                                            >
                                                {{ nomeOrigem(registro.origem) }}
                                            </p>
                                        </div>

                                        <div class="flex items-center gap-2 shrink-0">
                                            <button
                                                type="button"
                                                class="px-3 py-2 rounded-xl bg-indigo-600 text-white text-xs font-bold border-none cursor-pointer hover:bg-indigo-700"
                                                @click="abrirResumoSalvo(registro)"
                                            >
                                                Abrir
                                            </button>

                                            <button
                                                type="button"
                                                class="w-9 h-9 rounded-xl bg-white text-rose-600 border border-rose-100 cursor-pointer hover:bg-rose-50 disabled:opacity-60"
                                                :disabled="apagandoResumoId === registro.id"
                                                title="Apagar resumo"
                                                @click="apagarResumo(registro.id)"
                                            >
                                                <i
                                                    :class="
                                                        apagandoResumoId === registro.id
                                                            ? 'pi pi-spin pi-spinner'
                                                            : 'pi pi-trash'
                                                    "
                                                ></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm"
                        >
                            <h3
                                class="text-base font-bold text-gray-800 mb-4"
                            >
                                Histórico de eventos registrados
                            </h3>

                            <div
                                v-if="props.relatos.length === 0"
                                class="text-center py-8 text-gray-400 text-sm"
                            >
                                Nenhuma ocorrência registrada na sua linha do
                                tempo.
                            </div>

                            <div v-else class="space-y-4">
                                <div
                                    v-for="relato in props.relatos"
                                    :key="relato.id"
                                    class="p-4 rounded-2xl bg-gray-50/50 border border-gray-100 flex gap-4 items-start"
                                >
                                    <div
                                        class="w-8 h-8 rounded-full flex items-center justify-center shrink-0"
                                        :class="{
                                            'bg-rose-100 text-rose-700':
                                                relato.categoria === 'sintoma',
                                            'bg-amber-100 text-amber-700':
                                                relato.categoria === 'reacao',
                                            'bg-slate-100 text-slate-700':
                                                relato.categoria ===
                                                'observacao',
                                        }"
                                    >
                                        <i
                                            class="pi text-xs"
                                            :class="{
                                                'pi-exclamation-triangle':
                                                    relato.categoria ===
                                                    'sintoma',
                                                'pi-refresh':
                                                    relato.categoria ===
                                                    'reacao',
                                                'pi-info-circle':
                                                    relato.categoria ===
                                                    'observacao',
                                            }"
                                        ></i>
                                    </div>

                                    <div class="space-y-1 w-full">
                                        <div
                                            class="flex justify-between items-start gap-4"
                                        >
                                            <div>
                                                <h4
                                                    class="font-bold text-gray-800 text-sm"
                                                >
                                                    {{ relato.titulo }}
                                                </h4>

                                                <span
                                                    class="text-[10px] text-gray-400 font-medium capitalize"
                                                >
                                                    {{ relato.categoria }}
                                                </span>
                                            </div>

                                            <span
                                                class="text-xs font-semibold text-gray-400 font-mono whitespace-nowrap"
                                            >
                                                {{
                                                    formatarData(
                                                        relato.data_ocorrencia,
                                                    )
                                                }}
                                            </span>
                                        </div>

                                        <p
                                            class="text-xs text-gray-600 leading-relaxed pt-1"
                                        >
                                            {{ relato.relato }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div
            v-if="mostrarOpcoesResumo"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4"
            @click.self="fecharOpcoesResumo"
        >
            <div
                class="w-full max-w-2xl max-h-[90vh] overflow-y-auto bg-white rounded-3xl shadow-2xl"
            >
                <div
                    class="flex items-start justify-between gap-4 p-6 border-b border-gray-100"
                >
                    <div>
                        <h3 class="text-lg font-black text-gray-900">
                            Gerar sumário clínico
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">
                            Escolha o período e apenas as informações que devem
                            aparecer.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="w-9 h-9 rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 border-none cursor-pointer"
                        :disabled="carregandoResumo"
                        @click="fecharOpcoesResumo"
                    >
                        <i class="pi pi-times"></i>
                    </button>
                </div>

                <div class="p-6 space-y-6">
                    <div>
                        <label
                            class="block text-xs font-bold text-gray-600 mb-2 uppercase tracking-wider"
                        >
                            Período
                        </label>

                        <select
                            v-model="opcoesResumo.periodo"
                            class="w-full px-3 py-3 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-indigo-500"
                        >
                            <option value="30">Últimos 30 dias</option>
                            <option value="60">Últimos 60 dias</option>
                            <option value="90">Últimos 90 dias</option>
                            <option value="todos">
                                Todo o histórico disponível
                            </option>
                        </select>
                    </div>

                    <div>
                        <p
                            class="text-xs font-bold text-gray-600 mb-3 uppercase tracking-wider"
                        >
                            Informações do sumário
                        </p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <button
                                v-for="secao in secoesDisponiveis"
                                :key="secao.id"
                                type="button"
                                class="text-left p-4 rounded-2xl border transition cursor-pointer"
                                :class="
                                    opcoesResumo.secoes.includes(secao.id)
                                        ? 'border-indigo-500 bg-indigo-50'
                                        : 'border-gray-200 bg-white hover:border-indigo-200'
                                "
                                @click="alternarSecao(secao.id)"
                            >
                                <div class="flex items-start gap-3">
                                    <span
                                        class="w-5 h-5 rounded-md border flex items-center justify-center shrink-0 mt-0.5"
                                        :class="
                                            opcoesResumo.secoes.includes(
                                                secao.id,
                                            )
                                                ? 'bg-indigo-600 border-indigo-600 text-white'
                                                : 'border-gray-300 text-transparent'
                                        "
                                    >
                                        <i class="pi pi-check text-[10px]"></i>
                                    </span>

                                    <span>
                                        <strong
                                            class="block text-sm text-gray-800"
                                        >
                                            {{ secao.titulo }}
                                        </strong>
                                        <small
                                            class="block text-xs text-gray-500 mt-1 leading-relaxed"
                                        >
                                            {{ secao.descricao }}
                                        </small>
                                    </span>
                                </div>
                            </button>
                        </div>
                    </div>

                    <label
                        class="flex items-start gap-3 p-4 rounded-2xl bg-gray-50 cursor-pointer"
                    >
                        <input
                            v-model="opcoesResumo.incluir_perguntas"
                            type="checkbox"
                            class="mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                        />

                        <span>
                            <strong class="block text-sm text-gray-800">
                                Sugerir perguntas para o médico
                            </strong>
                            <small
                                class="block text-xs text-gray-500 mt-1"
                            >
                                Adiciona perguntas úteis para apoiar a consulta.
                            </small>
                        </span>
                    </label>

                    <p
                        v-if="erroResumo"
                        class="text-sm text-rose-600 bg-rose-50 border border-rose-100 rounded-xl px-4 py-3"
                    >
                        {{ erroResumo }}
                    </p>
                </div>

                <div
                    class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 p-6 border-t border-gray-100"
                >
                    <button
                        type="button"
                        class="px-5 py-3 rounded-xl bg-gray-100 text-gray-700 font-bold text-sm border-none cursor-pointer"
                        :disabled="carregandoResumo"
                        @click="fecharOpcoesResumo"
                    >
                        Cancelar
                    </button>

                    <button
                        type="button"
                        class="px-5 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm border-none cursor-pointer flex items-center justify-center gap-2 disabled:opacity-60"
                        :disabled="carregandoResumo"
                        @click="gerarResumo"
                    >
                        <i
                            v-if="carregandoResumo"
                            class="pi pi-spin pi-spinner"
                        ></i>
                        <i v-else class="pi pi-sparkles"></i>

                        {{
                            carregandoResumo
                                ? 'Gerando sumário...'
                                : 'Gerar sumário'
                        }}
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
