<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import axios from 'axios'
import { router, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({
    historico: {
        type: Array,
        default: () => []
    },
    estatisticas: {
        type: Object,
        default: () => ({ total: 0, ultimo_data: null, ultimo_local: null })
    },
    success: {
        type: String,
        default: null
    }
})

const mostrarFormulario = ref(false)
const modoCadastro = ref('ia')
const analisandoIA = ref(false)
const analiseConcluida = ref(false)
const mensagemIA = ref('')
const arquivoInput = ref(null)

const form = useForm({
    modo_cadastro: 'ia',
    motivo_atendimento: '',
    gravidade: 'Não informada',
    data_atendimento: '',
    local_atendimento: '',
    medico_nome: '',
    diagnostico: '',
    tratamento: '',
    exames_texto: '',
    medicamentos_texto: '',
    desfecho: '',
    acompanhamento: '',
    observacoes: '',
    arquivo: null
})

const limparCampos = () => {
    form.motivo_atendimento = ''
    form.gravidade = 'Não informada'
    form.data_atendimento = ''
    form.local_atendimento = ''
    form.medico_nome = ''
    form.diagnostico = ''
    form.tratamento = ''
    form.exames_texto = ''
    form.medicamentos_texto = ''
    form.desfecho = ''
    form.acompanhamento = ''
    form.observacoes = ''
}

const selecionarModo = (modo) => {
    modoCadastro.value = modo
    form.modo_cadastro = modo
    form.clearErrors()
    mensagemIA.value = ''
    analiseConcluida.value = false
    limparCampos()

    if (modo === 'manual') {
        form.arquivo = null

        if (arquivoInput.value) {
            arquivoInput.value.value = ''
        }
    }
}

const formatarData = (dataStr) => {
    if (!dataStr) return 'Data não informada'

    const data = new Date(dataStr)

    return `${data.toLocaleDateString('pt-BR')} às ${data.toLocaleTimeString('pt-BR', {
        hour: '2-digit',
        minute: '2-digit'
    })}`
}

const origemLabel = (origem) => {
    if (origem === 'simulador') return 'Relato no simulador'
    if (origem === 'documento') return 'Documento analisado pela IA'
    return 'Cadastro manual'
}

const classeGravidade = (gravidade) => {
    if (gravidade === 'Alta Gravidade') {
        return 'bg-rose-50 text-rose-700 border-rose-100'
    }

    if (gravidade === 'Média Gravidade') {
        return 'bg-amber-50 text-amber-700 border-amber-100'
    }

    if (gravidade === 'Baixa Gravidade') {
        return 'bg-emerald-50 text-emerald-700 border-emerald-100'
    }

    return 'bg-slate-50 text-slate-600 border-slate-200'
}

const exames = (registro) => Array.isArray(registro.exames_realizados)
    ? registro.exames_realizados
    : []

const medicamentos = (registro) => Array.isArray(registro.medicamentos)
    ? registro.medicamentos
    : []

const nomeMedicamento = (medicamento) => {
    return typeof medicamento === 'string'
        ? medicamento
        : medicamento?.nome || 'Medicamento'
}

const dosagemMedicamento = (medicamento) => {
    return typeof medicamento === 'object'
        ? medicamento?.dosagem
        : null
}

const selecionarArquivo = (event) => {
    form.arquivo = event.target.files?.[0] ?? null
    mensagemIA.value = ''
    analiseConcluida.value = false
}

const abrirSeletorArquivo = () => {
    arquivoInput.value?.click()
}

const textoMedicamentos = (itens) => {
    if (!Array.isArray(itens)) return ''

    return itens.map((item) => {
        if (typeof item === 'string') return item

        const nome = item?.nome || 'Medicamento'
        const dosagem = item?.dosagem || 'Não informada'

        return `${nome} | ${dosagem}`
    }).join('\n')
}

const analisarComIA = async () => {
    if (!form.arquivo) {
        mensagemIA.value = 'Selecione um PDF ou uma imagem do atendimento.'
        return
    }

    analisandoIA.value = true
    mensagemIA.value = ''
    form.clearErrors()

    try {
        const dadosFormulario = new FormData()
        dadosFormulario.append('arquivo', form.arquivo)

        const resposta = await axios.post(
            route('historico-clinico.analisar-ia'),
            dadosFormulario,
            {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            }
        )

        const dados = resposta.data?.dados || {}

        form.motivo_atendimento = dados.motivo_atendimento || ''
        form.gravidade = dados.gravidade || 'Não informada'
        form.data_atendimento = dados.data_atendimento || ''
        form.local_atendimento = dados.local_atendimento || ''
        form.medico_nome = dados.medico_nome || ''
        form.diagnostico = dados.diagnostico || ''
        form.tratamento = dados.tratamento || ''
        form.exames_texto = Array.isArray(dados.exames_realizados)
            ? dados.exames_realizados.join('\n')
            : ''
        form.medicamentos_texto = textoMedicamentos(dados.medicamentos)
        form.desfecho = dados.desfecho || ''
        form.acompanhamento = dados.acompanhamento || ''
        form.observacoes = dados.observacoes || ''

        analiseConcluida.value = true
        mensagemIA.value = 'Dados extraídos. Confira e corrija o que for necessário antes de salvar.'
    } catch (erro) {
        mensagemIA.value = erro.response?.data?.message
            || 'Não foi possível analisar o documento. Verifique o arquivo e tente novamente.'
    } finally {
        analisandoIA.value = false
    }
}

const salvar = () => {
    if (modoCadastro.value === 'ia' && !analiseConcluida.value) {
        mensagemIA.value = 'Analise o documento com a IA antes de salvar.'
        return
    }

    form.modo_cadastro = modoCadastro.value

    form.post(route('historico-clinico.store'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
            form.modo_cadastro = 'ia'
            form.gravidade = 'Não informada'
            modoCadastro.value = 'ia'
            analiseConcluida.value = false
            mensagemIA.value = ''
            mostrarFormulario.value = false

            if (arquivoInput.value) {
                arquivoInput.value.value = ''
            }
        }
    })
}

const excluir = (registro) => {
    const confirmou = window.confirm(
        'Deseja excluir este atendimento? O documento anexado também será removido.'
    )

    if (!confirmou) return

    router.delete(route('historico-clinico.destroy', registro.id), {
        preserveScroll: true
    })
}
</script>

<template>
    <AppLayout title="Histórico Clínico">
        <div class="p-6 max-w-7xl mx-auto w-full">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-6 text-left">
                <div>
                    <h1 class="text-3xl font-extrabold text-slate-900">
                        Histórico Clínico
                    </h1>
                    <p class="text-slate-500 mt-1">
                        Atendimentos de pronto-socorro, urgência e emergência vinculados somente ao seu perfil.
                    </p>
                </div>

                <button
                    type="button"
                    class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-bold text-white shadow-sm hover:bg-blue-700"
                    @click="mostrarFormulario = !mostrarFormulario"
                >
                    {{ mostrarFormulario ? 'Fechar formulário' : '+ Registrar atendimento' }}
                </button>
            </div>

            <div
                v-if="success"
                class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800"
            >
                {{ success }}
            </div>

            <form
                v-if="mostrarFormulario"
                class="mb-8 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm text-left"
                @submit.prevent="salvar"
            >
                <div class="border-b border-slate-100 px-6 py-6">
                    <h2 class="text-2xl font-extrabold text-slate-900">
                        Inserir Novo Atendimento
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Escolha o método de entrada para atualizar seu histórico clínico.
                    </p>
                </div>

                <div class="p-6">
                    <div class="mb-6 inline-flex rounded-2xl border border-slate-200 bg-slate-100 p-1">
                        <button
                            type="button"
                            class="rounded-xl px-5 py-3 text-sm font-bold transition"
                            :class="modoCadastro === 'ia'
                                ? 'bg-white text-blue-600 shadow-sm'
                                : 'text-slate-500 hover:text-slate-700'"
                            @click="selecionarModo('ia')"
                        >
                            ✨ Inteligência Artificial
                        </button>

                        <button
                            type="button"
                            class="rounded-xl px-5 py-3 text-sm font-bold transition"
                            :class="modoCadastro === 'manual'
                                ? 'bg-white text-blue-600 shadow-sm'
                                : 'text-slate-500 hover:text-slate-700'"
                            @click="selecionarModo('manual')"
                        >
                            ✎ Digitação Manual
                        </button>
                    </div>

                    <input
                        ref="arquivoInput"
                        type="file"
                        accept="image/*,application/pdf"
                        class="hidden"
                        @change="selecionarArquivo"
                    >

                    <div v-if="modoCadastro === 'ia'" class="mb-6">
                        <p class="mb-3 text-xs font-extrabold uppercase tracking-wide text-slate-700">
                            Carregar documento do atendimento *
                        </p>

                        <button
                            type="button"
                            class="flex min-h-48 w-full flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 px-6 py-8 text-center hover:border-blue-400 hover:bg-blue-50/40"
                            @click="abrirSeletorArquivo"
                        >
                            <span class="text-4xl text-slate-400">☁</span>
                            <span class="mt-3 font-extrabold text-blue-600">
                                {{ form.arquivo ? form.arquivo.name : 'Clique para selecionar o arquivo' }}
                            </span>
                            <span class="mt-2 text-sm text-slate-400">
                                Formatos aceitos: PDF, PNG ou JPG (Máx. 10MB)
                            </span>
                        </button>

                        <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center">
                            <button
                                type="button"
                                :disabled="analisandoIA || !form.arquivo"
                                class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-bold text-white disabled:cursor-not-allowed disabled:opacity-50"
                                @click="analisarComIA"
                            >
                                {{ analisandoIA ? 'Analisando documento...' : 'Preencher automaticamente com IA' }}
                            </button>

                            <p
                                v-if="mensagemIA"
                                class="text-sm font-semibold"
                                :class="analiseConcluida ? 'text-emerald-700' : 'text-amber-700'"
                            >
                                {{ mensagemIA }}
                            </p>
                        </div>

                        <p v-if="form.errors.arquivo" class="mt-2 text-xs font-semibold text-red-600">
                            {{ form.errors.arquivo }}
                        </p>
                    </div>

                    <div
                        v-if="modoCadastro === 'manual' || analiseConcluida"
                        class="rounded-2xl border border-slate-200 bg-slate-50/40 p-5"
                    >
                        <div v-if="modoCadastro === 'ia'" class="mb-5 rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-800">
                            Confira todos os campos. A IA organiza o conteúdo do documento, mas você pode corrigir qualquer informação antes de salvar.
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <label class="text-sm font-semibold text-slate-700">
                                Motivo do atendimento *
                                <input v-model="form.motivo_atendimento" type="text" class="mt-1 w-full rounded-xl border-slate-300" required>
                                <span v-if="form.errors.motivo_atendimento" class="text-xs text-red-600">{{ form.errors.motivo_atendimento }}</span>
                            </label>

                            <label class="text-sm font-semibold text-slate-700">
                                Gravidade *
                                <select v-model="form.gravidade" class="mt-1 w-full rounded-xl border-slate-300">
                                    <option>Não informada</option>
                                    <option>Baixa Gravidade</option>
                                    <option>Média Gravidade</option>
                                    <option>Alta Gravidade</option>
                                </select>
                            </label>

                            <label class="text-sm font-semibold text-slate-700">
                                Data e hora *
                                <input v-model="form.data_atendimento" type="datetime-local" class="mt-1 w-full rounded-xl border-slate-300" required>
                            </label>

                            <label class="text-sm font-semibold text-slate-700">
                                Hospital ou unidade *
                                <input v-model="form.local_atendimento" type="text" class="mt-1 w-full rounded-xl border-slate-300" required>
                            </label>

                            <label class="text-sm font-semibold text-slate-700">
                                Médico(a)
                                <input v-model="form.medico_nome" type="text" class="mt-1 w-full rounded-xl border-slate-300">
                            </label>

                            <label class="text-sm font-semibold text-slate-700">
                                Desfecho
                                <input v-model="form.desfecho" type="text" class="mt-1 w-full rounded-xl border-slate-300" placeholder="Ex.: Alta após observação">
                            </label>

                            <label class="text-sm font-semibold text-slate-700 md:col-span-2">
                                Diagnóstico informado
                                <textarea v-model="form.diagnostico" rows="2" class="mt-1 w-full rounded-xl border-slate-300"></textarea>
                            </label>

                            <label class="text-sm font-semibold text-slate-700 md:col-span-2">
                                Tratamento realizado
                                <textarea v-model="form.tratamento" rows="2" class="mt-1 w-full rounded-xl border-slate-300"></textarea>
                            </label>

                            <label class="text-sm font-semibold text-slate-700">
                                Exames realizados
                                <textarea v-model="form.exames_texto" rows="3" class="mt-1 w-full rounded-xl border-slate-300" placeholder="Um por linha ou separados por vírgula"></textarea>
                            </label>

                            <label class="text-sm font-semibold text-slate-700">
                                Medicamentos aplicados
                                <textarea v-model="form.medicamentos_texto" rows="3" class="mt-1 w-full rounded-xl border-slate-300" placeholder="Ex.: Dipirona | 500 mg"></textarea>
                            </label>

                            <label class="text-sm font-semibold text-slate-700 md:col-span-2">
                                Acompanhamento recomendado
                                <textarea v-model="form.acompanhamento" rows="2" class="mt-1 w-full rounded-xl border-slate-300"></textarea>
                            </label>

                            <label class="text-sm font-semibold text-slate-700 md:col-span-2">
                                Observações pessoais
                                <textarea v-model="form.observacoes" rows="2" class="mt-1 w-full rounded-xl border-slate-300"></textarea>
                            </label>

                            <label v-if="modoCadastro === 'manual'" class="text-sm font-semibold text-slate-700 md:col-span-2">
                                Documento do atendimento (opcional)
                                <input type="file" accept="image/*,application/pdf" class="mt-1 block w-full rounded-xl border border-slate-300 bg-white p-2" @change="selecionarArquivo">
                                <span v-if="form.errors.arquivo" class="text-xs text-red-600">{{ form.errors.arquivo }}</span>
                            </label>
                        </div>

                        <div class="mt-5 flex justify-end">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-bold text-white disabled:opacity-50"
                            >
                                {{ form.processing ? 'Salvando...' : 'Salvar atendimento' }}
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <div
                v-if="estatisticas.total > 0"
                class="bg-blue-50 border border-blue-100 rounded-2xl p-4 flex items-center gap-3 text-left text-sm text-blue-900 mb-8 shadow-sm"
            >
                <div class="p-2 bg-blue-600 text-white rounded-full shrink-0">ⓘ</div>
                <div>
                    <span class="font-extrabold">Total de atendimentos: {{ estatisticas.total }}</span>
                    <span class="text-slate-300 mx-2">•</span>
                    <span>
                        Último atendimento em <strong>{{ estatisticas.ultimo_data }}</strong>
                        <template v-if="estatisticas.ultimo_local"> — {{ estatisticas.ultimo_local }}</template>
                    </span>
                </div>
            </div>

            <div v-if="historico.length" class="relative border-l-2 border-slate-200 ml-4 md:ml-6 space-y-8 text-left w-full">
                <div v-for="registro in historico" :key="registro.id" class="relative pl-8 w-full">
                    <span class="absolute -left-[13px] top-1.5 rounded-full h-6 w-6 bg-blue-600 text-white flex items-center justify-center ring-4 ring-blue-100 shadow-sm">•</span>

                    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm w-full hover:shadow-md transition">
                        <div class="flex flex-col gap-4 border-b border-slate-100 pb-4 mb-4 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="text-xl font-extrabold text-slate-800">{{ registro.motivo_atendimento }}</h2>
                                    <span class="rounded-full border px-2.5 py-0.5 text-xs font-bold" :class="classeGravidade(registro.gravidade)">
                                        {{ registro.gravidade || 'Não informada' }}
                                    </span>
                                    <span class="rounded-full bg-violet-50 px-2.5 py-0.5 text-xs font-semibold text-violet-700">
                                        {{ origemLabel(registro.origem) }}
                                    </span>
                                </div>

                                <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-sm font-medium text-slate-400">
                                    <span>🕒 {{ formatarData(registro.data_atendimento) }}</span>
                                    <span>📍 {{ registro.local_atendimento || 'Local não informado' }}</span>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <a
                                    v-if="registro.arquivo_path"
                                    :href="route('historico-clinico.visualizar', registro.id)"
                                    target="_blank"
                                    rel="noopener"
                                    class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-700 no-underline hover:bg-slate-50"
                                >
                                    Visualizar documento
                                </a>
                                <a
                                    v-if="registro.arquivo_path"
                                    :href="route('historico-clinico.download', registro.id)"
                                    class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-700 no-underline hover:bg-slate-50"
                                >
                                    Baixar documento
                                </a>
                                <a
                                    :href="route('historico-clinico.relatorio', registro.id)"
                                    class="rounded-xl bg-blue-600 px-3 py-2 text-sm font-bold text-white no-underline hover:bg-blue-700"
                                >
                                    Resumo PDF
                                </a>
                                <button type="button" class="rounded-xl border border-red-200 px-3 py-2 text-sm font-bold text-red-600 hover:bg-red-50" @click="excluir(registro)">
                                    Excluir
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                            <div class="space-y-4 lg:col-span-7">
                                <div>
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">Médico(a)</h4>
                                    <p class="mt-1 text-sm font-medium text-slate-700">{{ registro.medico_nome || 'Não informado' }}</p>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">Diagnóstico informado</h4>
                                    <p class="mt-1 rounded-xl border border-slate-100 bg-slate-50 p-3 text-sm font-semibold text-slate-700">{{ registro.diagnostico || 'Não informado' }}</p>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">Tratamento</h4>
                                    <p class="mt-1 text-sm leading-relaxed text-slate-600">{{ registro.tratamento || 'Não informado' }}</p>
                                </div>
                            </div>

                            <div class="space-y-4 lg:col-span-5 lg:border-l lg:border-slate-100 lg:pl-6">
                                <div>
                                    <h4 class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-400">Exames realizados</h4>
                                    <div v-if="exames(registro).length" class="flex flex-wrap gap-1.5">
                                        <span v-for="exame in exames(registro)" :key="exame" class="rounded-lg border border-blue-100 bg-blue-50 px-2.5 py-1 text-xs font-bold text-blue-700">
                                            {{ exame }}
                                        </span>
                                    </div>
                                    <p v-else class="text-xs italic text-slate-400">Nenhum exame informado.</p>
                                </div>

                                <div>
                                    <h4 class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-400">Medicamentos aplicados</h4>
                                    <div v-if="medicamentos(registro).length" class="flex flex-wrap gap-1.5">
                                        <span v-for="(med, indice) in medicamentos(registro)" :key="`${nomeMedicamento(med)}-${indice}`" class="rounded-lg border border-emerald-100 bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">
                                            {{ nomeMedicamento(med) }}
                                            <span v-if="dosagemMedicamento(med)" class="font-normal">({{ dosagemMedicamento(med) }})</span>
                                        </span>
                                    </div>
                                    <p v-else class="text-xs italic text-slate-400">Nenhuma medicação informada.</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 flex flex-col gap-2 border-t border-slate-100 pt-4 text-sm text-slate-600">
                            <p><strong class="text-slate-800">Desfecho:</strong> {{ registro.desfecho || 'Não informado' }}</p>
                            <p v-if="registro.acompanhamento"><strong class="text-slate-800">Acompanhamento:</strong> {{ registro.acompanhamento }}</p>
                            <p v-if="registro.observacoes"><strong class="text-slate-800">Observações:</strong> {{ registro.observacoes }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="mx-auto mt-12 max-w-xl rounded-3xl border border-dashed border-slate-300 bg-white p-12 text-center text-slate-500">
                <div class="text-4xl">🏥</div>
                <p class="mt-3 text-lg font-bold text-slate-700">Nenhum atendimento registrado</p>
                <p class="mt-1 text-sm">
                    Registre manualmente, envie um documento pelo simulador ou relate um atendimento e confirme o salvamento.
                </p>
                <button type="button" class="mt-5 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-bold text-white" @click="mostrarFormulario = true">
                    Registrar primeiro atendimento
                </button>
            </div>
        </div>
    </AppLayout>
</template>
