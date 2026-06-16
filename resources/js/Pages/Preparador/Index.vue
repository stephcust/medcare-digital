<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    relatorioGerado: Object
});

const form = useForm({
    especialidade: '',
    queixa_principal: ''
});

const enviarFormulario = () => {
    form.post(route('preparador.gerar'));
};
</script>

<template>
    <AppLayout title="Preparador de Consulta">
        <div class="max-w-4xl mx-auto py-8 px-4 text-left">
            <div class="mb-6">
                <h2 class="text-xl font-bold text-slate-800">Preparador de Consulta Inteligente</h2>
                <p class="text-xs text-slate-400">Escolha a especialidade do médico. Nossa IA filtrará seus exames, receitas e sintomas para criar um sumário clínico direto para o profissional.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Painel de Entrada -->
                <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs h-fit">
                    <h3 class="text-sm font-bold text-slate-700 mb-3">Dados da Consulta</h3>
                    <form @submit.prevent="enviarFormulario" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1">Especialidade Médica</label>
                            <input v-model="form.especialidade" type="text" placeholder="Ex: Cardiologista, Clínico Geral" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm" required />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1">O que está sentindo? (Queixa)</label>
                            <textarea v-model="form.queixa_principal" placeholder="Descreva brevemente seus sintomas atuais para correlacionar com o histórico." class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm h-28 resize-none" required></textarea>
                        </div>
                        <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-2.5 rounded-xl text-xs transition border-none cursor-pointer" :disabled="form.processing">
                            Compilar Sumário
                        </button>
                    </form>
                </div>

                <!-- Painel do Relatório Inteligente Extraído -->
                <div class="md:col-span-2">
                    <div v-if="!relatorioGerado" class="bg-slate-50 border-2 border-dashed border-slate-200 rounded-2xl p-8 text-center text-slate-400 text-sm flex flex-col items-center justify-center min-h-[300px]">
                        <i class="pi pi-sliders-h text-2xl mb-2 text-slate-300"></i>
                        Preencha os dados ao lado para estruturar o relatório clínico da IA.
                    </div>

                    <div v-else class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm space-y-5 animate-fade-in">
                        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                            <div>
                                <span class="bg-indigo-50 text-indigo-700 font-extrabold text-[9px] px-2 py-0.5 rounded-sm uppercase">Sumário de Encaminhamento</span>
                                <h3 class="text-base font-black text-slate-800 mt-1">Especialidade: {{ relatorioGerado.especialidade }}</h3>
                            </div>
                            <span class="text-[10px] text-slate-400 font-mono">Gerado em: {{ relatorioGerado.data_emissao }}</span>
                        </div>

                        <div>
                            <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Análise de Sintomas do Ecossistema</h4>
                            <p class="text-xs text-slate-600 bg-slate-50/50 p-3 rounded-xl border border-slate-100 leading-relaxed">{{ relatorioGerado.analise_sintomas }}</p>
                        </div>

                        <div>
                            <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Histórico de Dados Anexados Encontrados</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                <div class="bg-blue-50/40 p-3 rounded-xl border border-blue-100/50">
                                    <span class="font-bold text-blue-800 block mb-1"><i class="pi pi-file-pdf"></i> Últimos Laudos Cruzados:</span>
                                    <ul class="list-disc pl-4 space-y-0.5 text-slate-600">
                                        <li v-for="exame in relatorioGerado.dados_relevantes.exames" :key="exame">{{ exame }}</li>
                                    </ul>
                                </div>
                                <div class="bg-purple-50/40 p-3 rounded-xl border border-purple-100/50">
                                    <span class="font-bold text-purple-800 block mb-1"><i class="pi pi-paperclip"></i> Medicações Ativas:</span>
                                    <ul class="list-disc pl-4 space-y-0.5 text-slate-600">
                                        <li v-for="med in relatorioGerado.dados_relevantes.medicamentos_ativos" :key="med">{{ med }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="bg-amber-50/50 border border-amber-100 rounded-xl p-4">
                            <h4 class="text-xs font-bold text-amber-800 uppercase tracking-wider mb-2 flex items-center gap-1">
                                <i class="pi pi-question-circle"></i> Sugestões de Perguntas para fazer ao seu Médico:
                            </h4>
                            <ul class="space-y-2 text-xs text-slate-700">
                                <li v-for="(pergunta, i) in relatorioGerado.sugestao_perguntas" :key="i" class="flex gap-2 items-start">
                                    <span class="font-bold text-amber-600">{{ i+1 }}.</span>
                                    <span>{{ pergunta }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
