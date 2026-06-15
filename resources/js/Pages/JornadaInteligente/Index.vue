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
        resumo.value = 'Não consegui gerar o resumo agora. Tente novamente em alguns instantes.'
    } finally {
        carregandoResumo.value = false
    }
}

const formatarData = (data) => {
    if (!data) {
        return 'Sem data'
    }

    return new Date(data).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    })
}

const nomeCategoria = (categoria) => {
    const categorias = {
        sintoma: 'Sintoma',
        consulta: 'Consulta',
        medicacao: 'Medicação',
        exame: 'Exame',
        vacina: 'Vacina',
        outro: 'Outro',
    }

    return categorias[categoria] || categoria
}
</script>

<template>
    <AppLayout title="Jornada Inteligente">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">
                        Jornada Inteligente
                    </h1>
                    <p class="text-gray-500 text-sm sm:text-base mt-1">
                        Registre relatos de saúde e gere um resumo organizado para consultas.
                    </p>
                </div>

                <Link
                    href="/dashboard"
                    class="inline-flex items-center justify-center bg-white text-gray-700 px-4 py-2 rounded-full shadow text-sm font-semibold hover:bg-gray-100"
                >
                    ← Voltar
                </Link>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
                        <h2 class="text-lg font-bold text-gray-800">
                            Registrar relato
                        </h2>

                        <p class="text-sm text-gray-500 mt-1 mb-5">
                            Conte algo que aconteceu com você. Esses relatos podem entrar no resumo para o médico.
                        </p>

                        <form @submit.prevent="salvarRelato" class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">
                                    Categoria
                                </label>

                                <select
                                    v-model="form.categoria"
                                    class="w-full rounded-2xl border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                    <option value="sintoma">Sintoma</option>
                                    <option value="consulta">Consulta</option>
                                    <option value="medicacao">Medicação</option>
                                    <option value="exame">Exame</option>
                                    <option value="vacina">Vacina</option>
                                    <option value="outro">Outro</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">
                                    Título
                                </label>

                                <input
                                    v-model="form.titulo"
                                    type="text"
                                    class="w-full rounded-2xl border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="Ex: Dor de cabeça"
                                />

                                <p v-if="form.errors.titulo" class="text-xs text-red-600 mt-1">
                                    {{ form.errors.titulo }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">
                                    Data da ocorrência
                                </label>

                                <input
                                    v-model="form.data_ocorrencia"
                                    type="date"
                                    class="w-full rounded-2xl border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                                />

                                <p v-if="form.errors.data_ocorrencia" class="text-xs text-red-600 mt-1">
                                    {{ form.errors.data_ocorrencia }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">
                                    Relato
                                </label>

                                <textarea
                                    v-model="form.relato"
                                    rows="5"
                                    class="w-full rounded-2xl border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="Ex: Hoje tive dor de cabeça depois do almoço."
                                ></textarea>

                                <p v-if="form.errors.relato" class="text-xs text-red-600 mt-1">
                                    {{ form.errors.relato }}
                                </p>
                            </div>

                            <label class="flex items-start gap-2 text-sm text-gray-600">
                                <input
                                    v-model="form.incluir_no_resumo"
                                    type="checkbox"
                                    class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                />

                                <span>
                                    Incluir este relato no resumo para consulta.
                                </span>
                            </label>

                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="w-full bg-blue-600 text-white py-3 rounded-2xl text-sm font-bold hover:bg-blue-700 disabled:opacity-50"
                            >
                                {{ form.processing ? 'Salvando...' : 'Salvar relato' }}
                            </button>
                        </form>
                    </div>
                </div>

                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-5">
                            <div>
                                <h2 class="text-lg font-bold text-gray-800">
                                    Linha do tempo
                                </h2>

                                <p class="text-sm text-gray-500 mt-1">
                                    Relatos registrados pelo próprio usuário.
                                </p>
                            </div>

                            <button
                                @click="gerarResumo"
                                :disabled="carregandoResumo"
                                class="bg-green-600 text-white px-5 py-3 rounded-2xl text-sm font-bold hover:bg-green-700 disabled:opacity-50"
                            >
                                {{ carregandoResumo ? 'Gerando...' : 'Gerar resumo para médico' }}
                            </button>
                        </div>

                        <div v-if="props.relatos.length === 0" class="bg-gray-50 rounded-2xl p-5 text-sm text-gray-500">
                            Nenhum relato registrado ainda.
                        </div>

                        <div v-else class="space-y-4">
                            <div
                                v-for="relato in props.relatos"
                                :key="relato.id"
                                class="border border-gray-100 rounded-2xl p-4 bg-gray-50"
                            >
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-2">
                                    <div>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700">
                                            {{ nomeCategoria(relato.categoria) }}
                                        </span>

                                        <h3 class="font-bold text-gray-800 mt-2">
                                            {{ relato.titulo || 'Relato sem título' }}
                                        </h3>
                                    </div>

                                    <span class="text-xs text-gray-500">
                                        {{ formatarData(relato.data_ocorrencia) }}
                                    </span>
                                </div>

                                <p class="text-sm text-gray-700 whitespace-pre-line">
                                    {{ relato.relato }}
                                </p>

                                <p class="text-xs text-gray-500 mt-3">
                                    {{ relato.incluir_no_resumo ? 'Incluído no resumo para consulta.' : 'Não incluído no resumo.' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div v-if="resumo" class="bg-blue-600 rounded-3xl p-6 text-white shadow-lg">
                        <h2 class="text-lg font-bold mb-3">
                            Resumo gerado pela IA
                        </h2>

                        <p class="text-blue-100 text-sm whitespace-pre-line">
                            {{ resumo }}
                        </p>

                        <p class="text-xs text-blue-100 mt-4">
                            Este resumo organiza dados e relatos informados no MedCare. Ele não substitui avaliação médica profissional.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>