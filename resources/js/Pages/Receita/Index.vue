<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';

defineProps({
    paciente: Object,
    receitas: Array,
    success: String
});

const deletarReceita = (id) => {
    if (confirm('Deseja realmente remover esta receita do seu histórico digital?')) {
        router.delete(route('receitas.destroy', id));
    }
};

const formatarData = (dataStr) => {
    if (!dataStr) return '';
    return new Date(dataStr).toLocaleDateString('pt-BR', { timeZone: 'UTC' });
};
</script>

<template>
    <AppLayout title="Minhas Receitas">
        <div class="w-full max-w-5xl mx-auto py-6 px-4 sm:px-6 lg:px-8">

            <div class="mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Prescrições e Receitas</h1>
                    <p class="text-sm text-gray-500">Histórico digitalizado de orientações médicas e medicamentos.</p>
                </div>
            </div>

            <div v-if="success" class="mb-6 bg-green-50 border border-green-200 text-green-700 p-4 rounded-xl text-sm font-semibold">
                {{ success }}
            </div>

            <div v-if="receitas.length === 0" class="bg-white rounded-2xl p-12 text-center border border-gray-100 shadow-sm">
                <i class="pi pi-file text-4xl text-gray-300 mb-3 block"></i>
                <p class="text-gray-500 text-sm">Nenhuma receita médica localizada para a sua conta.</p>
            </div>

            <div v-else class="relative border-s border-gray-200 ms-2 sm:ms-4 space-y-6">
                <div v-for="receita in receitas" :key="receita.id" class="mb-6 ms-6 relative">
                    <span class="absolute -start-[31px] top-1.5 flex h-4 w-4 items-center justify-center rounded-full ring-4 ring-white bg-purple-600"></span>

                    <div class="bg-white p-5 lg:p-7 rounded-2xl border border-gray-100 shadow-sm hover:border-gray-200 transition-colors">
                        <div class="flex justify-between items-start gap-2">
                            <div class="w-full">
                                <span class="text-xs font-bold text-gray-400 block uppercase tracking-wider mb-1">
                                    Emissão: {{ formatarData(receita.data_emissao) }}
                                </span>
                                <h3 class="text-base font-bold text-gray-800 flex items-center gap-2 mb-3">
                                    <i class="pi pi-user text-purple-600 text-sm"></i> {{ receita.medico }}
                                </h3>

                                <div class="bg-gray-50 p-4 rounded-xl text-sm text-gray-700 whitespace-pre-line leading-relaxed border border-gray-100">
                                    {{ receita.medicamentos }}
                                </div>

                                <div class="mt-3 text-xs text-gray-400 flex items-center gap-2" v-if="receita.caminho_arquivo">
                                    <i class="pi pi-paperclip"></i> Possui documento digital assinado anexo.
                                </div>
                            </div>

                            <span class="text-[10px] font-bold px-2 py-1 rounded-md uppercase bg-teal-50 text-teal-700 border border-teal-100 shrink-0">
                                Sincronizado via API
                            </span>
                        </div>

                        <div class="mt-4 pt-4 border-t border-gray-50 flex items-center justify-end">
                            <button @click="deletarReceita(receita.id)" class="text-xs font-medium text-red-500 hover:text-red-700">
                                <i class="pi pi-trash animate-none"></i> Remover Histórico
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
