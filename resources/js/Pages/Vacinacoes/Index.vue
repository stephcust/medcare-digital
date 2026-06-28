<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    paciente: Object,
    vacinacoes: Array,
});

// Configuração reativa das abas e estados da IA
const modoAbas = ref('ia'); // Inicializa por padrão na aba Inteligente
const lendoArquivo = ref(false);
const erroIA = ref(null);
const iaSucesso = ref(false);
const toast = useToast();

const form = useForm({
    modo_cadastro: 'ia',
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
    erroIA.value = null;

    if (aba === 'manual') {
        iaSucesso.value = false;
        form.reset('nome_vacina', 'fabricante', 'lote', 'observacoes', 'data_proxima_dose');
        form.numero_dose = '1ª Dose';
    }
};

// Captura do arquivo sem disparar requisição imediata
const handleFileUpload = (event) => {
    const arquivoSelecionado = event.target.files[0];
    if (!arquivoSelecionado) return;

    form.comprovante = arquivoSelecionado;
    erroIA.value = null;
    iaSucesso.value = false; // Reseta estado de sucesso se trocar o arquivo
};

// Disparo manual pelo botão dedicado
const iniciarAnaliseIA = () => {
    if (form.comprovante) {
        executarLeituraIA(form.comprovante);
    }
};

const executarLeituraIA = async (arquivo) => {
    lendoArquivo.value = true;
    erroIA.value = null;
    iaSucesso.value = false;

    const formData = new FormData();
    formData.append('comprovante', arquivo);

    try {
        const response = await axios.post(route('vacinacoes.analisar-ia'), formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });

        if (response.data && response.data.success) {
            const extraido = response.data.dados;

            // Preenchimento automático reativo das informações lidas pela IA
            form.nome_vacina = extraido.nome_vacina || '';
            form.fabricante = extraido.fabricante || '';
            form.lote = extraido.lote || '';
            form.numero_dose = extraido.numero_dose || '1ª Dose';
            form.data_aplicacao = extraido.data_aplicacao || new Date().toISOString().slice(0, 10);
            form.data_proxima_dose = extraido.data_proxima_dose || '';

            iaSucesso.value = true;
        } else {
            throw new Error('A resposta do servidor falhou.');
        }
    } catch (error) {
        console.error("Erro ao analisar comprovante:", error);
        const msg = error.response?.data?.message || "Não conseguimos extrair dados desse documento.";
        erroIA.value = `${msg} Por favor, digite manualmente alternando a aba superior.`;

        // Joga para preenchimento manual em caso de erro para não travar o fluxo
        alternarAba('manual');
    } finally {
        lendoArquivo.value = false;
    }
};

const salvarVacina = () => {
    form.post(route('vacinacoes.store', props.paciente.id), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            form.numero_dose = '1ª Dose';
            form.data_aplicacao = new Date().toISOString().slice(0, 10);
            iaSucesso.value = false;
            if (document.getElementById('file-input')) {
                document.getElementById('file-input').value = "";
            }
        },
        onError: (error) => {
                toast.add({
                severity: "error",
                summary: "Ocorreu um erro: "+ error.message,
                life: 5000,
            });
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
                        <button type="button" @click="alternarAba('ia')" :class="['w-full py-2 text-xs font-bold rounded-lg border-none cursor-pointer transition', modoAbas === 'ia' ? 'bg-white text-indigo-600 shadow-xs' : 'text-gray-500 bg-transparent']">
                            <i class="pi pi-sparkles mr-1"></i> Inteligência Artificial
                        </button>
                        <button type="button" @click="alternarAba('manual')" :class="['w-full py-2 text-xs font-bold rounded-lg border-none cursor-pointer transition', modoAbas === 'manual' ? 'bg-white text-indigo-600 shadow-xs' : 'text-gray-500 bg-transparent']">
                            <i class="pi pi-pencil mr-1"></i> Digitação Manual
                        </button>
                    </div>

                    <form @submit.prevent="salvarVacina" class="space-y-4">

                        <div v-if="modoAbas === 'ia'" class="space-y-4">
                            <div class="border-2 border-dashed border-gray-200 rounded-2xl p-6 text-center hover:border-indigo-400 transition bg-slate-50/50 relative">

                                <div v-if="lendoArquivo" class="absolute inset-0 bg-white/95 backdrop-blur-xs flex flex-col items-center justify-center rounded-2xl z-10 p-4">
                                    <div class="w-8 h-8 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin mb-2"></div>
                                    <p class="text-[11px] font-bold text-indigo-600 animate-pulse">MedCare IA lendo comprovante...</p>
                                </div>

                                <div class="space-y-2">
                                    <i class="pi pi-cloud-upload text-3xl text-gray-300 block"></i>
                                    <div class="text-xs text-gray-600">
                                        <label class="relative cursor-pointer font-bold text-indigo-600 hover:text-indigo-500">
                                            <span>Clique para selecionar o arquivo</span>
                                            <input id="file-input" type="file" class="sr-only" @change="handleFileUpload" accept=".pdf,.png,.jpg,.jpeg" />
                                        </label>
                                    </div>
                                    <span class="text-[10px] text-gray-400 block">Formatos: JPG, PNG ou PDF (Máx 10MB)</span>
                                </div>

                                <div v-if="form.comprovante" class="mt-4 flex flex-col items-center gap-2">
                                    <div class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 text-[11px] px-2.5 py-1 rounded-lg font-semibold border border-emerald-100">
                                        <i class="pi pi-file-pdf"></i> {{ form.comprovante.name }}
                                    </div>

                                    <button
                                        v-if="!iaSucesso"
                                        type="button"
                                        @click="iniciarAnaliseIA"
                                        :disabled="lendoArquivo"
                                        class="mt-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-xl text-xs font-bold shadow-xs border-none cursor-pointer flex items-center justify-center gap-1.5 transition"
                                    >
                                        <i class="pi pi-sparkles"></i> Analisar com Inteligência Artificial
                                    </button>
                                </div>
                            </div>

                            <div v-if="erroIA" class="bg-red-50 border border-red-100 text-red-700 text-[11px] p-3 rounded-xl flex items-start gap-1.5">
                                <i class="pi pi-exclamation-circle mt-0.5"></i>
                                <span>{{ erroIA }}</span>
                            </div>
                        </div>

                        <div v-if="modoAbas === 'manual' || iaSucesso" class="space-y-4 pt-2 animate-fade-in">

                            <div v-if="iaSucesso && modoAbas === 'ia'" class="bg-indigo-50 border border-indigo-100 p-3 rounded-xl text-indigo-800 text-[11px] font-semibold flex items-center gap-1.5">
                                <i class="pi pi-sparkles text-indigo-500 animate-pulse"></i>
                                <span>Dados extraídos! Revise antes de salvar.</span>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wider">Nome do Imunizante *</label>
                                <input v-model="form.nome_vacina" type="text" placeholder="Ex: Antitetânica, COVID-19, Gripe" class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-indigo-500" required />
                                <div v-if="form.errors.nome_vacina" class="text-red-500 text-xs mt-1">{{ form.errors.nome_vacina }}</div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wider">Dose *</label>
                                    <select v-model="form.numero_dose" class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-indigo-500" required>
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

                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wider">Data de Aplicação *</label>
                                <input v-model="form.data_aplicacao" type="date" class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-indigo-500" required />
                                <div v-if="form.errors.data_aplicacao" class="text-red-500 text-xs mt-1">{{ form.errors.data_aplicacao }}</div>
                            </div>

                            <div class="border-t border-gray-100 pt-3">
                                <label class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wider">Próxima Dose (Agendamento)</label>
                                <input v-model="form.data_proxima_dose" type="date" class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-indigo-500" />
                                <div v-if="form.errors.data_proxima_dose" class="text-red-500 text-xs mt-1">{{ form.errors.data_proxima_dose }}</div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wider">Observações adicionais</label>
                                <textarea v-model="form.observacoes" rows="2" placeholder="Notas sobre reações ou orientações..." class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-indigo-500"></textarea>
                            </div>

                            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-xl text-xs transition border-none cursor-pointer shadow-xs flex items-center justify-center gap-2" :disabled="form.processing">
                                <i v-if="form.processing" class="pi pi-spin pi-spinner"></i>
                                {{ form.processing ? 'Gravando Registro...' : 'Salvar Registro na Carteira' }}
                            </button>
                        </div>
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
                                            <p v-if="vacina.observacoes" class="italic mt-1 text-gray-400">{{ vacina.observacoes }}</p>
                                        </div>
                                    </div>

                                    <div
                                        v-if="vacina.arquivo_path"
                                        class="flex flex-wrap items-center justify-end gap-2"
                                    >
                                        <a
                                            :href="route('vacinacoes.visualizar', vacina.id)"
                                            target="_blank"
                                            rel="noopener"
                                            class="flex items-center gap-1 text-xs font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-xl transition no-underline"
                                        >
                                            <i class="pi pi-eye"></i>
                                            Visualizar
                                        </a>

                                        <a
                                            :href="route('vacinacoes.download', vacina.id)"
                                            class="flex items-center gap-1 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 px-3 py-1.5 rounded-xl transition no-underline"
                                        >
                                            <i class="pi pi-download"></i>
                                            Baixar
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
