<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';

defineProps({
    paciente: Object,
    vacinacoes: Array,
});

const deletarVacina = (id) => {
    if (confirm('Deseja realmente remover este registro de vacinação da sua carteira digital?')) {
        router.delete(route('vacinacoes.destroy', id));
    }
};

const formatarData = (dataStr) => {
    if (!dataStr) return '';
    return new Date(dataStr).toLocaleDateString('pt-BR', { timeZone: 'UTC' });
};
</script>

<template>
    <AppLayout title="Minhas Vacinas">
        <div class="w-full max-w-5xl mx-auto py-6 px-4 sm:px-6 lg:px-8">

            <div class="mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Caderneta de Vacinação</h1>
                    <p class="text-sm text-gray-500">Histórico unificado de imunizantes e doses aplicadas.</p>
                </div>
            </div>

            <div v-if="vacinacoes.length === 0" class="bg-white rounded-2xl p-12 text-center border border-gray-100 shadow-sm">
                <i class="pi pi-folder-open text-4xl text-gray-300 mb-3 block"></i>
                <p class="text-gray-500 text-sm">Nenhuma vacina localizada na sua conta.</p>
            </div>

            <div v-else class="relative border-s border-gray-200 ms-2 sm:ms-4 space-y-6">
                <div v-for="vacina in vacinacoes" :key="vacina.id" class="mb-6 ms-6 relative">
                    <span class="absolute -start-[31px] top-1.5 flex h-4 w-4 items-center justify-center rounded-full ring-4 ring-white bg-green-600"></span>

                    <div class="bg-white p-5 lg:p-7 rounded-2xl border border-gray-100 shadow-sm hover:border-gray-200 transition-colors">
                        <div class="flex justify-between items-start gap-2">
                            <div>
                                <span class="text-xs font-bold text-gray-400 block uppercase tracking-wider mb-1">
                                    Aplicação: {{ formatarData(vacina.data_aplicacao) }}
                                </span>
                                <h3 class="text-base font-bold text-gray-800 flex items-center gap-2">
                                    {{ vacina.nome_vacina }}
                                    <span class="bg-blue-100 text-blue-700 text-[10px] px-2 py-0.5 rounded-full font-bold">
                                        {{ vacina.numero_dose }}
                                    </span>
                                </h3>

                                <div class="mt-2 space-y-1 text-sm text-gray-500">
                                    <p v-if="vacina.lote"><i class="pi pi-barcode text-xs me-1"></i> Lote: {{ vacina.lote }}</p>
                                    <p v-if="vacina.fabricante"><i class="pi pi-building text-xs me-1"></i> Fabricante: {{ vacina.fabricante }}</p>
                                    <p v-if="vacina.data_proxima_dose" class="text-amber-600 font-medium">
                                        <i class="pi pi-calendar-plus text-xs me-1"></i> Próxima Dose: {{ formatarData(vacina.data_proxima_dose) }}
                                    </p>
                                </div>
                            </div>

                            <!-- <span :class="['text-[10px] font-bold px-2 py-1 rounded-md uppercase', vacina.origem === 'api' ? 'bg-teal-50 text-teal-700 border border-teal-100' : 'bg-purple-50 text-purple-700 border border-purple-100']">
                                {{ vacina.origem === 'api' ? 'API / Clínica' : 'Manual' }}
                            </span> -->
                        </div>

                        <div class="mt-4 pt-4 border-t border-gray-50 flex items-center justify-end">
                            <button @click="deletarVacina(vacina.id)" class="text-xs font-medium text-red-500 hover:text-red-700">
                                <i class="pi pi-trash"></i> Excluir Registro
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
