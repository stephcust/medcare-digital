<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    paciente: Object,
    vacinacoes: Array,
});

const modoAbas = ref('manual'); // manual ou arquivo

const form = useForm({
    modo_cadastro: 'manual',
    nome_vacina: '',
    fabricante: '',
    lote: '',
    numero_dose: '1ª Dose',
    data_aplicacao: new Date().toISOString().slice(0, 10),
    data_proxima_dose: '',
    observacoes: '',
    comprovante: null
});

const alternarAba = (aba) => {
    modoAbas.value = aba;
    form.modo_cadastro = aba;
};

const salvarVacina = () => {
    form.post(route('vacinacoes.store', props.paciente.id), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('nome_vacina', 'fabricante', 'lote', 'observacoes', 'comprovante');
            form.numero_dose = '1ª Dose';
            document.getElementById('file-input').value = "";
        }
    });
};

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
        <div class="w-full max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8 text-left">

            <div class="mb-8">
                <h1 class="text-2xl font-black text-gray-800 tracking-tight">Caderneta de Vacinação</h1>
                <p class="text-sm text-gray-500">Cadastre manualmente ou faça o upload do seu cartão de vacina físico.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm h-fit">
                    <h3 class="text-base font-bold text-gray-800 mb-4">Registrar Imunização</h3>

                    <div class="flex bg-gray-100 p-1 rounded-xl mb-4">
                        <button @click="alternarAba('manual')" :class="['w-full py-2 text-xs font-bold rounded-lg border-none cursor-pointer transition', modoAbas === 'manual' ? 'bg-white text-indigo-600 shadow-xs' : 'text-gray-500 bg-transparent']">
                            <i class="pi pi-pencil mr-1"></i> Manual
                        </button>
                        <button @click="alternarAba('arquivo')" :class="['w-full py-2 text-xs font-bold rounded-lg border-none cursor-pointer transition', modoAbas === 'arquivo' ? 'bg-white text-indigo-600 shadow-xs' : 'text-gray-500 bg-transparent']">
                            <i class="pi pi-upload mr-1"></i> Upload de Cartão
                        </button>
                    </div>

                    <form @submit.prevent="salvarVacina" class="space-y-4">

                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wider">Data de Aplicação</label>
                            <input v-model="form.data_aplicacao" type="date" class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-indigo-500" required />
                        </div>

                        <div v-if="modoAbas === 'manual'" class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wider">Nome do Imunizante</label>
                                <input v-model="form.nome_vacina" type="text" placeholder="Ex: Antitetânica, COVID-19, Gripe" class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-indigo-500" :required="modoAbas === 'manual'" />
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wider">Dose</label>
                                    <select v-model="form.numero_dose" class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-indigo-500">
                                        <option value="1ª Dose">1ª Dose</option>
                                        <option value="2ª Dose">2ª Dose</option>
                                        <option value="3ª Dose">3ª Dose</option>
                                        <option value="Dose Única">Dose Única</option>
                                        <option value="Reforço">Reforço</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wider">Lote</label>
                                    <input v-model="form.lote" type="text" placeholder="Ex: FX2211" class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-indigo-500" />
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wider">Fabricante / Laboratório</label>
                                <input v-model="form.fabricante" type="text" placeholder="Ex: Fiocruz, Pfizer, Butantan" class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-indigo-500" />
                            </div>
                        </div>

                        <div v-else class="space-y-4">
                            <div class="border-2 border-dashed border-gray-200 rounded-2xl p-4 text-center hover:border-indigo-400 transition bg-slate-50/50">
                                <i class="pi pi-cloud-upload text-3xl text-gray-300 mb-2 block"></i>
                                <span class="text-xs text-gray-500 block mb-2">Formatos aceitos: JPG, PNG ou PDF (Máx 10MB)</span>
                                <input id="file-input" type="file" @input="form.comprovante = $event.target.files[0]" class="text-xs text-gray-600 max-w-full" :required="modoAbas === 'arquivo'" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wider">Identificador Opcional (Nome)</label>
                                <input v-model="form.nome_vacina" type="text" placeholder="Ex: Foto do Cartão de Infância" class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-indigo-500" />
                            </div>
                        </div>

                        <div class="border-t border-gray-50 pt-3">
                            <label class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wider">Próxima Dose (Agendamento)</label>
                            <input v-model="form.data_proxima_dose" type="date" class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-indigo-500" />
                        </div>

                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 rounded-xl text-xs transition border-none cursor-pointer shadow-xs flex items-center justify-center gap-2" :disabled="form.processing">
                            <i v-if="form.processing" class="pi pi-spin pi-spinner"></i>
                            {{ form.processing ? 'Salvando...' : 'Salvar Registro' }}
                        </button>
                    </form>
                </div>

                <div class="lg:col-span-2 space-y-4">
                    <div v-if="vacinacoes.length === 0" class="bg-white rounded-3xl p-12 text-center border border-gray-100 shadow-sm">
                        <i class="pi pi-folder-open text-4xl text-gray-300 mb-3 block"></i>
                        <p class="text-gray-500 text-sm">Nenhuma vacina localizada na sua conta.</p>
                    </div>

                    <div v-else class="relative border-s border-gray-200 ms-4 space-y-6">
                        <div v-for="vacina in vacinacoes" :key="vacina.id" class="ms-6 relative">
                            <span class="absolute -start-[31px] top-1.5 flex h-4 w-4 items-center justify-center rounded-full ring-4 ring-white bg-indigo-600 text-white"></span>

                            <div class="bg-white p-5 lg:p-6 rounded-3xl border border-gray-100 shadow-sm hover:border-gray-200 transition">
                                <div class="flex justify-between items-start gap-4">
                                    <div>
                                        <span class="text-xs font-bold text-gray-400 block uppercase tracking-wider mb-1">
                                            Aplicação: {{ formatarData(vacina.data_aplicacao) }}
                                        </span>
                                        <h3 class="text-base font-black text-gray-800 flex items-center gap-2 flex-wrap">
                                            {{ vacina.nome_vacina }}
                                            <span class="bg-indigo-50 text-indigo-700 text-[10px] px-2.5 py-0.5 rounded-md font-bold">
                                                {{ vacina.numero_dose }}
                                            </span>
                                        </h3>

                                        <div class="mt-3 space-y-1 text-xs text-gray-500">
                                            <p v-if="vacina.lote"><i class="pi pi-barcode mr-1"></i> <strong>Lote:</strong> {{ vacina.lote }}</p>
                                            <p v-if="vacina.fabricante"><i class="pi pi-building mr-1"></i> <strong>Fabricante:</strong> {{ vacina.fabricante }}</p>
                                            <p v-if="vacina.data_proxima_dose" class="text-amber-600 font-medium">
                                                <i class="pi pi-calendar-plus mr-1"></i> <strong>Próxima Dose Prevista:</strong> {{ formatarData(vacina.data_proxima_dose) }}
                                            </p>
                                        </div>
                                    </div>

                                    <div v-if="vacina.arquivo_url">
                                        <a :href="vacina.arquivo_url" target="_blank" class="flex items-center gap-1 text-xs font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-xl transition no-underline">
                                            <i class="pi pi-paperclip"></i> Ver Documento
                                        </a>
                                    </div>
                                </div>

                                <div class="mt-4 pt-3 border-t border-gray-50 flex items-center justify-end">
                                    <button @click="deletarVacina(vacina.id)" class="text-xs font-semibold text-red-500 hover:text-red-700 bg-transparent border-none cursor-pointer flex items-center gap-1">
                                        <i class="pi pi-trash"></i> Remover
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>
