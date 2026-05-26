<script setup>
import { ref, computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';

const propriedades = defineProps({
    paciente: Object,
    receitas: Array,
    success: String
});

// Estado do filtro ativo ('Todas', 'Ativas', 'Concluídas', 'Expiradas')
const filtroSelecionado = ref('Todas');

const formatarData = (dataStr) => {
    if (!dataStr) return '';
    return new Date(dataStr).toLocaleDateString('pt-BR', { timeZone: 'UTC' });
};

// Deletar registro mantendo o fluxo
const deletarReceita = (id) => {
    if (confirm('Deseja realmente remover esta prescrição do seu histórico?')) {
        router.delete(route('receitas.destroy', id));
    }
};

// 1. Cálculos dos Cards Informativos superiores (Métricas do topo)
const totalReceitasAtivas = computed(() => {
    return propriedades.receitas.filter(r => r.status === 'Ativa').length;
});

const totalMedicamentosEmUso = computed(() => {
    return propriedades.receitas
        .filter(r => r.status === 'Ativa')
        .reduce((acumulador, r) => acumulador + (Array.isArray(r.medicamentos) ? r.medicamentos.length : 0), 0);
});

// 2. Filtro reativo da listagem
const receitasFiltradas = computed(() => {
    if (filtroSelecionado.value === 'Todas') return propriedades.receitas;
    return propriedades.receitas.filter(r => r.status === filtroSelecionado.value);
});
</script>

<template>
    <AppLayout title="Receitas Médicas">
        <div class="w-full max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 bg-gray-50 min-h-screen">

            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Receitas Médicas</h1>
                <p class="text-sm text-gray-500">Visualize e imprima suas prescrições e medicamentos</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex flex-col justify-between h-36">
                    <div>
                        <span class="text-sm font-medium text-gray-400 block">Receitas Ativas</span>
                        <span class="text-4xl font-bold text-green-600 mt-2 block">{{ totalReceitasAtivas }}</span>
                    </div>
                    <div class="text-xs text-green-700 flex items-center gap-1.5 pt-2 border-t border-gray-50">
                        <i class="pi pi-file"></i> Prescrições válidas
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex flex-col justify-between h-36">
                    <div>
                        <span class="text-sm font-medium text-gray-400 block">Medicamentos em Uso</span>
                        <span class="text-4xl font-bold text-blue-600 mt-2 block">{{ totalMedicamentosEmUso }}</span>
                    </div>
                    <div class="text-xs text-blue-700 flex items-center gap-1.5 pt-2 border-t border-gray-50">
                        <i class="pi pi-link"></i> Medicamentos ativos
                    </div>
                </div>
            </div>

            <div class="flex gap-2 mb-6 overflow-x-auto pb-2">
                <button
                    v-for="status in ['Todas', 'Ativas', 'Concluídas', 'Expiradas']"
                    :key="status"
                    @click="filtroSelecionado = status"
                    :class="['px-4 py-1.5 text-xs font-semibold rounded-full border transition-all shrink-0',
                        filtroSelecionado === status
                        ? 'bg-primary-900 text-white border-primary-900 shadow-sm'
                        : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'
                    ]"
                >
                    {{ status }}
                </button>
            </div>

            <div class="space-y-6">
                <div
                    v-for="receita in receitasFiltradas"
                    :key="receita.id"
                    class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden"
                >
                    <div class="p-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="text-lg font-bold text-gray-800">{{ receita.medico }}</h3>
                                <span class="bg-green-100 text-green-700 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase">
                                    {{ receita.status }}
                                </span>
                            </div>
                            <div class="text-xs font-semibold text-blue-600 flex items-center gap-3">
                                <span>{{ receita.especialidade }}</span>
                                <span class="text-gray-300">|</span>
                                <span class="text-gray-500 font-normal"><i class="pi pi-calendar"></i> {{ formatarData(receita.data_emissao) }}</span>
                                <span class="text-gray-500 font-normal">Válida até {{ formatarData(receita.data_validade) }}</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 self-end sm:self-center">
                            <button class="bg-white hover:bg-gray-50 border border-gray-200 text-gray-700 text-xs font-semibold px-4 py-2 rounded-xl inline-flex items-center gap-1.5 shadow-sm transition">
                                <i class="pi pi-eye text-xs"></i> Visualizar
                            </button>
                            <button @click="window.print()" class="bg-white hover:bg-gray-50 border border-gray-200 text-gray-700 text-xs font-semibold px-4 py-2 rounded-xl inline-flex items-center gap-1.5 shadow-sm transition">
                                <i class="pi pi-print text-xs"></i> Imprimir
                            </button>
                            <button @click="deletarReceita(receita.id)" class="text-gray-400 hover:text-red-500 text-xs p-2 transition ml-1" title="Remover histórico">
                                <i class="pi pi-trash"></i>
                            </button>
                        </div>
                    </div>

                    <div class="p-6 bg-gray-50/50 space-y-4">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Medicamentos Prescritos:</h4>

                        <div class="space-y-2">
                            <div
                                v-for="(med, idx) in receita.medicamentos"
                                :key="idx"
                                class="bg-white p-4 rounded-xl border border-gray-100 shadow-xs flex items-start justify-between"
                            >
                                <div>
                                    <h5 class="text-sm font-bold text-gray-800">{{ med.nome }} <span class="text-gray-500 font-normal ml-1">{{ med.dosagem }}</span></h5>
                                    <div class="text-xs text-gray-500 mt-1 flex flex-wrap gap-x-4 gap-y-1">
                                        <span><strong>Dosagem:</strong> 1 {{ med.nome.toLowerCase() === 'doxiciclina' ? 'cápsula' : 'comprimido' }}</span>
                                        <span><strong>Frequência:</strong> {{ med.frequencia }}</span>
                                        <span><strong>Duração:</strong> {{ med.duracao }}</span>
                                    </div>
                                </div>
                                <i class="pi pi-link text-blue-400 text-xs mt-1"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="receitasFiltradas.length === 0" class="bg-white rounded-2xl p-12 text-center border border-gray-200 shadow-sm">
                    <i class="pi pi-info-circle text-3xl text-gray-300 mb-2 block"></i>
                    <p class="text-gray-500 text-sm">Nenhuma prescrição encontrada para a categoria "{{ filtroSelecionado }}".</p>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
