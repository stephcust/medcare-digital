<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    exame: Object
});

const formatarData = (dataStr) => {
    return new Date(dataStr).toLocaleDateString('pt-BR', {timeZone: 'UTC'});
};
</script>

<template>
    <AppLayout title="Detalhes do Exame">
        <div class="w-full max-w-4xl mx-auto py-6 px-4 sm:px-6 lg:px-8">

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-6 lg:p-8 bg-gray-50/50 border-b border-gray-100">
                    <h2 class="text-xl lg:text-2xl font-bold text-gray-800">{{ exame.nome }}</h2>
                    <p class="text-xs text-gray-500 mt-1">Carregado via dispositivo seguro de saúde.</p>
                </div>

                <div class="p-6 lg:p-8 space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-sm lg:text-base">
                        <div>
                            <span class="text-gray-400 block font-medium">Categoria</span>
                            <span class="text-gray-700 font-semibold">{{ exame.tipo }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block font-medium">Data de Realização</span>
                            <span class="text-gray-700 font-semibold">{{ formatarData(exame.data_realizacao) }}</span>
                        </div>
                    </div>

                    <div class="text-sm lg:text-base pt-2">
                        <span class="text-gray-400 block font-medium">Instituição Responsável</span>
                        <span class="text-gray-700 font-semibold">{{ exame.laboratorio }}</span>
                    </div>

                    <div class="pt-6 border-t border-gray-50">
                        <a :href="route('exames.download', exame.id)" target="_blank"
                           class="w-full sm:w-auto bg-blue-600 text-white py-3 px-5 rounded-xl text-sm font-bold shadow-sm hover:bg-blue-700 transition-colors inline-flex items-center justify-center gap-2">
                            <i class="pi pi-download"></i> Baixar Arquivo de Laudo (PDF)
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
