<script setup>
import { ref, computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { router, Link } from '@inertiajs/vue3';

const propriedades = defineProps({
    paciente: Object,
    receitas: Array,
    success: String
});

const filtroSelecionado = ref('Todas');

const formatarData = (dataStr) => {
    if (!dataStr) return '';
    return new Date(dataStr).toLocaleDateString('pt-BR', { timeZone: 'UTC' });
};

const deletarReceita = (id) => {
    if (confirm('Deseja realmente remover esta prescrição médica do seu histórico? O arquivo associado na nuvem também será excluído.')) {
        router.delete(route('receitas.destroy', id), {
            preserveScroll: true
        });
    }
};

// Métricas dinâmicas do topo
const totalReceitasAtivas = computed(() => {
    return propriedades.receitas.filter(r => r.status === 'Ativa').length;
});

const totalMedicamentosEmUso = computed(() => {
    return propriedades.receitas
        .filter(r => r.status === 'Ativa')
        .reduce((acumulador, r) => acumulador + (Array.isArray(r.medicamentos) ? r.medicamentos.length : 0), 0);
});

// Filtro inteligente da listagem
const receitasFiltradas = computed(() => {
    if (filtroSelecionado.value === 'Todas') return propriedades.receitas;
    return propriedades.receitas.filter(r => r.status === filtroSelecionado.value);
});
</script>

<template>
    <AppLayout title="Minhas Receitas Médicas">
        <div class="w-full max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 text-left">

            <div v-if="propriedades.success" class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold rounded-2xl flex items-center gap-2">
                <i class="pi pi-check-circle text-emerald-500 text-sm"></i>
                <span>{{ propriedades.success }}</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
                <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                        <i class="pi pi-file-edit text-lg"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Prescrições Ativas</p>
                        <h3 class="text-xl font-black text-slate-800 mt-0.5">{{ totalReceitasAtivas }}</h3>
                    </div>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                        <i class="pi pi-table text-lg"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Medicamentos Monitorados</p>
                        <h3 class="text-xl font-black text-slate-800 mt-0.5">{{ totalMedicamentosEmUso }}</h3>
                    </div>
                </div>
            </div>

            <div class="flex gap-2 mb-6 overflow-x-auto pb-1">
                <button v-for="cat in ['Todas', 'Ativa', 'Concluída', 'Expirada']" :key="cat"
                    @click="filtroSelecionado = cat"
                    :class="['px-4 py-2 text-xs font-bold rounded-xl border transition-all cursor-pointer whitespace-nowrap',
                            filtroSelecionado === cat
                            ? 'bg-blue-600 text-white border-blue-600 shadow-xs'
                            : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50']">
                    {{ cat === 'Ativa' ? 'Ativas' : cat === 'Concluída' ? 'Concluídas' : cat === 'Expirada' ? 'Expiradas' : cat }}
                </button>
            </div>

            <div class="space-y-4">
                <div v-for="receita in receitasFiltradas" :key="receita.id"
                    class="bg-white rounded-2xl border border-slate-100 shadow-xs p-5 sm:p-6 transition hover:shadow-md">

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-500 mt-0.5">
                                <i class="pi pi-user-md text-base"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-black text-slate-800 tracking-tight">{{ receita.medico }}</h4>
                                <p class="text-xs font-medium text-slate-400 mt-0.5">{{ receita.especialidade }}</p>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <div class="text-left sm:text-right">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Emissão: {{ formatarData(receita.data_emissao) }}</p>
                                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mt-0.5">Validade: {{ formatarData(receita.data_validade) }}</p>
                            </div>

                            <span :class="['px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider',
                                            receita.status === 'Ativa' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-slate-100 text-slate-600 border border-slate-200']">
                                {{ receita.status }}
                            </span>

                            <div v-if="receita.arquivo_url">
                                <a :href="receita.arquivo_url" target="_blank"
                                   class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 px-3 py-2 rounded-xl transition no-underline border border-blue-100 shadow-2xs">
                                    <i class="pi pi-eye"></i> Visualizar Receita
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="py-4">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-3">Medicamentos Prescritos</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div v-for="(med, idx) in receita.medicamentos" :key="idx"
                                class="bg-slate-50/70 p-3.5 rounded-xl border border-slate-100 flex items-start justify-between">
                                <div>
                                    <h5 class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 bg-blue-500 rounded-full"></span>
                                        {{ med.nome }} <span class="text-slate-400 font-medium font-mono text-[11px] ml-1">({{ med.dosagem }})</span>
                                    </h5>
                                    <div class="text-[11px] text-slate-500 mt-2 space-y-0.5 pl-3">
                                        <p><strong>Frequência:</strong> {{ med.frequencia }}</p>
                                        <p><strong>Duração:</strong> {{ med.duracao }}</p>
                                    </div>
                                </div>
                                <span v-if="receita.origem === 'api'" title="Identificado via Inteligência Artificial" class="text-[10px] text-blue-500 font-bold bg-blue-50 px-1.5 py-0.5 rounded-md flex items-center gap-1 h-fit">
                                    <i class="pi pi-sparkles text-[9px]"></i> IA
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-[10px] font-medium text-slate-400">Inserido em: {{ new Date(receita.created_at).toLocaleDateString('pt-BR') }}</span>
                        <button @click="deletarReceita(receita.id)"
                                class="text-xs font-bold text-red-500 hover:text-red-700 bg-transparent border-none cursor-pointer flex items-center gap-1 transition">
                            <i class="pi pi-trash"></i> Remover Prescrição
                        </button>
                    </div>

                </div>

                <div v-if="receitasFiltradas.length === 0" class="bg-white rounded-2xl p-12 text-center border border-slate-200 shadow-xs">
                    <i class="pi pi-folder-open text-3xl text-slate-300 mb-2 block"></i>
                    <p class="text-slate-500 text-sm font-medium">Nenhuma receita cadastrada ou encontrada para a categoria "{{ filtroSelecionado }}".</p>
                    <Link :href="route('receitas.create', propriedades.paciente.id)" class="inline-block bg-blue-600 text-white font-bold text-xs px-4 py-2 rounded-xl mt-4 border-none no-underline shadow-xs hover:bg-blue-700">
                        Anexar Primeira Receita
                    </Link>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
