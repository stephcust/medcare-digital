<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import axios from 'axios'

const props = defineProps({
    relatos: {
        type: Array,
        default: () => [],
    },
})

const resumo = ref('')
const carregandoResumo = ref(false)

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

const gerarResumo = async () => {
    carregandoResumo.value = true
    resumo.value = ''

    try {
        const response = await axios.post(route('jornada-inteligente.resumo'))
        resumo.value = response.data.resumo
    } catch (error) {
        resumo.value = 'Não consegui compilar os dados para a sua consulta agora. Tente novamente.'
    } finally {
        carregandoResumo.value = false
    }
}

const formatarData = (dataString) => {
    if (!dataString) return ''
    const [ano, mes, dia] = dataString.split('-')
    return `${dia}/${mes}/${ano}`
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
                        <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
                            <h3 class="text-base font-bold text-gray-800 mb-2">
                                Registrar Linha do Tempo
                            </h3>
                            <p class="text-xs text-gray-500 mb-4">
                                Insira sintomas, dores ou observações cotidianas para enriquecer a base de dados do seu prontuário.
                            </p>

                            <form @submit.prevent="salvarRelato" class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wider">Categoria</label>
                                    <select v-model="form.categoria" class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-indigo-500">
                                        <option value="sintoma">Sintoma / Desconforto</option>
                                        <option value="reacao">Reação a Medicamento</option>
                                        <option value="observacao">Observação Geral</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wider">Título Simples</label>
                                    <input v-model="form.titulo" type="text" placeholder="Ex: Dor de cabeça forte, Febre alta" class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-indigo-500" required />
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wider">Data do Evento</label>
                                    <input v-model="form.data_ocorrencia" type="date" class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-indigo-500" required />
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wider">Relato Detalhado</label>
                                    <textarea v-model="form.relato" placeholder="Descreva o que sentiu, intensidade e quanto tempo durou..." class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm h-28 resize-none focus:outline-none focus:border-indigo-500" required></textarea>
                                </div>

                                <div class="flex items-center gap-2 py-1">
                                    <input type="checkbox" id="incluir" v-model="form.incluir_no_resumo" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                                    <label密 for="incluir" class="text-xs text-gray-600 font-medium select-none cursor-pointer">Usar este relato na preparação de consultas</label密>
                                </div>

                                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 rounded-xl text-xs transition border-none cursor-pointer shadow-xs" :disabled="form.processing">
                                    Salvar na Jornada
                                </button>
                            </form>
                        </div>

                        <div class="bg-gradient-to-br from-indigo-900 to-slate-900 p-6 rounded-3xl text-white shadow-sm">
                            <h4 class="text-sm font-black mb-1">Vai ao Médico?</h4>
                            <p class="text-xs text-indigo-200 mb-4 leading-relaxed">
                                Peça para a IA compilar toda a sua jornada (relatos, exames e receitas) em um único sumário estruturado para apresentar diretamente no consultório.
                            </p>
                            <button @click="gerarResumo" class="w-full bg-white text-indigo-900 hover:bg-indigo-50 font-bold py-3 rounded-xl text-xs transition border-none cursor-pointer flex items-center justify-center gap-2 shadow-xs" :disabled="carregandoResumo">
                                <i v-if="carregandoResumo" class="pi pi-spin pi-spinner"></i>
                                <i v-else class="pi pi-sliders-h"></i>
                                {{ carregandoResumo ? 'Processando Histórico...' : 'Compilar Sumário para Consulta' }}
                            </button>
                        </div>
                    </div>

                    <div class="lg:col-span-2 space-y-6">

                        <div v-if="resumo" class="bg-indigo-50 border border-indigo-100 rounded-3xl p-6 shadow-xs">
                            <div class="flex justify-between items-start border-b border-indigo-100 pb-3 mb-4">
                                <div>
                                    <span class="bg-indigo-600 text-white font-extrabold text-[9px] px-2 py-0.5 rounded-sm uppercase tracking-wider">Documento Auxiliar</span>
                                    <h3 class="text-base font-black text-indigo-950 mt-1">Sumário de Preparação Clínico</h3>
                                </div>
                                <i class="pi pi-sparkles text-indigo-600 text-lg"></i>
                            </div>

                            <p class="text-sm text-indigo-900 whitespace-pre-line leading-relaxed">
                                {{ resumo }}
                            </p>

                            <p class="text-[11px] text-indigo-400 mt-5 italic">
                                * Este sumário organiza e agrupa as informações inseridas no ecossistema MedCare com o intuito de otimizar o tempo e a triagem da sua consulta médica.
                            </p>
                        </div>

                        <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
                            <h3 class="text-base font-bold text-gray-800 mb-4">
                                Histórico de Eventos Registrados
                            </h3>

                            <div v-if="relatos.length === 0" class="text-center py-8 text-gray-400 text-sm">
                                Nenhuma ocorrência registrada na sua linha do tempo.
                            </div>

                            <div v-else class="space-y-4">
                                <div v-for="relato in relatos" :key="relato.id" class="p-4 rounded-2xl bg-gray-50/50 border border-gray-100 flex gap-4 items-start">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0" :class="{
                                        'bg-rose-100 text-rose-700': relato.categoria === 'sintoma',
                                        'bg-amber-100 text-amber-700': relato.categoria === 'reacao',
                                        'bg-slate-100 text-slate-700': relato.categoria === 'observacao'
                                    }">
                                        <i class="pi text-xs" :class="{
                                            'pi-exclamation-triangle': relato.categoria === 'sintoma',
                                            'pi-refresh': relato.categoria === 'reacao',
                                            'pi-info-circle': relato.categoria === 'observacao'
                                        }"></i>
                                    </div>
                                    <div class="space-y-1 w-full">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h4 class="font-bold text-gray-800 text-sm">{{ relato.titulo }}</h4>
                                                <span class="text-[10px] text-gray-400 font-medium capitalize">{{ relato.categoria }}</span>
                                            </div>
                                            <span class="text-xs font-semibold text-gray-400 font-mono">
                                                {{ formatarData(relato.data_ocorrencia) }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-600 leading-relaxed pt-1">
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
    </AppLayout>
</template>
