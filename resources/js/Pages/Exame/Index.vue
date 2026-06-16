<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';

defineProps({
    exames: Array,
    success: String
});

const deletarExame = (id) => {
    if (confirm('Deseja realmente remover este exame da sua carteira digital?')) {
        router.delete(route('exames.destroy', id));
    }
};

const formatarData = (dataStr) => {
    return new Date(dataStr).toLocaleDateString('pt-BR', {timeZone: 'UTC'});
};
</script>

<template>
    <AppLayout title="Meus Exames">
        <div class="w-full max-w-5xl mx-auto py-6 px-4 sm:px-6 lg:px-8">

            <div class="mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Linha do Tempo de Saúde</h1>
                    <p class="text-sm text-gray-500">Histórico unificado de exames e laudos clínicos.</p>
                </div>
            </div>

            <div v-if="success" class="mb-6 bg-green-50 border border-green-200 text-green-700 p-4 rounded-xl text-sm font-semibold">
                {{ success }}
            </div>

            <div v-if="exames.length === 0" class="bg-white rounded-2xl p-12 text-center border border-gray-100 shadow-sm">
                <i class="pi pi-folder-open text-4xl text-gray-300 mb-3 block"></i>
                <p class="text-gray-500 text-sm">Nenhum exame localizado na sua conta.</p>
            </div>

            <div v-else class="relative border-s border-gray-200 ms-2 sm:ms-4 space-y-6">
                <div v-for="exame in exames" :key="exame.id" class="mb-6 ms-6 relative">
                    <span :class="['absolute -start-[31px] top-1.5 flex h-4 w-4 items-center justify-center rounded-full ring-4 ring-white', exame.visualizado ? 'bg-gray-300' : 'bg-blue-600 animate-pulse']"></span>

                    <div class="bg-white p-5 lg:p-7 rounded-2xl border border-gray-100 shadow-sm hover:border-gray-200 transition-colors">
                        <div class="flex justify-between items-start gap-2">
                            <div>
                                <span class="text-xs font-bold text-gray-400 block uppercase tracking-wider mb-1">
                                    {{ formatarData(exame.data_realizacao) }}
                                </span>
                                <h3 class="text-base font-bold text-gray-800 flex items-center gap-2">
                                    {{ exame.nome }}
                                    <span v-if="!exame.visualizado" class="bg-blue-100 text-blue-700 text-[10px] px-2 py-0.5 rounded-full font-bold">Novo</span>
                                </h3>
                                <p class="text-sm text-gray-500 mt-1">
                                    <i class="pi pi-building text-xs me-1"></i> {{ exame.laboratorio }}
                                </p>
                            </div>

                            <span :class="['text-[10px] font-bold px-2 py-1 rounded-md uppercase', exame.origem === 'api' ? 'bg-teal-50 text-teal-700 border border-teal-100' : 'bg-purple-50 text-purple-700 border border-purple-100']">
                                {{ exame.origem === 'api' ? 'Clínica' : 'Manual' }}
                            </span>
                        </div>

                        <div class="mt-4 pt-4 border-t border-gray-50 flex items-center justify-between">
                            <Link :href="route('exames.show', exame.id)" class="text-sm font-semibold text-blue-600 hover:underline inline-flex items-center">
                                <i class="pi pi-eye me-1 text-xs"></i> Abrir Laudo
                            </Link>

                            <button @click="deletarExame(exame.id)" class="text-xs font-medium text-red-500 hover:text-red-700">
                                <i class="pi pi-trash"></i> Excluir
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
