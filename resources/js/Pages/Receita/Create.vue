<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    paciente: Object
});

const modoAbas = ref('ia');
const lendoArquivo = ref(false);
const erroIA = ref(null);
const iaSucesso = ref(false);

const form = useForm({
    modo_cadastro: 'ia',
    medico: '',
    especialidade: '',
    data_emissao: '',
    data_validade: '',
    medicamentos: [], // Array de objetos { nome, dosagem, frequencia, duracao }
    arquivo: null,
});

const alternarAba = (aba) => {
    modoAbas.value = aba;
    form.modo_cadastro = aba;
    erroIA.value = null;

    if (aba === 'manual') {
        iaSucesso.value = false;
        form.reset('medico', 'especialidade', 'data_emissao', 'data_validade', 'medicamentos');
        adicionarMedicamentoManual(); // Inicia com pelo menos um campo vazio
    }
};

const handleFileUpload = (event) => {
    const arquivoSelecionado = event.target.files[0];
    if (!arquivoSelecionado) return;

    form.arquivo = arquivoSelecionado;
    erroIA.value = null;

    if (modoAbas.value === 'ia') {
        executarLeituraIA(arquivoSelecionado);
    }
};

const executarLeituraIA = async (arquivo) => {
    lendoArquivo.value = true;
    erroIA.value = null;
    iaSucesso.value = false;

    const formData = new FormData();
    formData.append('arquivo', arquivo);

    try {
        const response = await axios.post(route('receitas.analisar-ia'), formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });

        if (response.data && response.data.success) {
            const extraido = response.data.dados;

            form.medico = extraido.medico || '';
            form.especialidade = extraido.especialidade || 'Clínico Geral';
            form.data_emissao = extraido.data_emissao || '';
            form.data_validade = extraido.data_validade || '';
            form.medicamentos = extraido.medicamentos || [];

            iaSucesso.value = true;
        } else {
            throw new Error();
        }
    } catch (error) {
        console.error(error);
        erroIA.value = "Não fomos capazes de processar esta receita automaticamente. Insira os dados manualmente.";
        alternarAba('manual');
    } finally {
        lendoArquivo.value = false;
    }
};

const adicionarMedicamentoManual = () => {
    form.medicamentos.push({ nome: '', dosagem: '', frequencia: '', duracao: '' });
};

const removerMedicamentoManual = (index) => {
    form.medicamentos.splice(index, 1);
};

const submitForm = () => {
    form.post(route('receitas.store', props.paciente.id), {
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            iaSucesso.value = false;
        },
    });
};
</script>

<template>
    <AppLayout title="Anexar Receita Médica">
        <div class="w-full max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8 text-left">
            <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">

                <div class="p-6 sm:p-8 border-b border-slate-100 bg-slate-50/50">
                    <h1 class="text-xl lg:text-2xl font-black text-slate-800 tracking-tight">Digitalizar Receita Médica</h1>
                    <p class="text-xs text-slate-500 mt-1">Armazene e catalogue suas prescrições de forma inteligente na nuvem.</p>
                </div>

                <div class="px-6 sm:px-8 pt-6">
                    <div class="flex bg-slate-100 p-1 rounded-2xl max-w-sm border border-slate-200">
                        <button type="button" @click="alternarAba('ia')"
                            :class="['w-full py-2.5 text-xs font-bold rounded-xl border-none cursor-pointer transition-all duration-200 flex items-center justify-center gap-2',
                                    modoAbas === 'ia' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 bg-transparent hover:text-slate-800']">
                            <i class="pi pi-sparkles"></i> Análise Inteligente
                        </button>
                        <button type="button" @click="alternarAba('manual')"
                            :class="['w-full py-2.5 text-xs font-bold rounded-xl border-none cursor-pointer transition-all duration-200 flex items-center justify-center gap-2',
                                    modoAbas === 'manual' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 bg-transparent hover:text-slate-800']">
                            <i class="pi pi-pencil"></i> Inserção Manual
                        </button>
                    </div>
                </div>

                <form @submit.prevent="submitForm" class="p-6 sm:p-8 space-y-6">

                    <div v-if="modoAbas === 'ia'" class="space-y-4">
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider">Documento da Receita (PDF ou Imagem) *</label>
                        <div class="border-2 border-slate-300 border-dashed rounded-2xl p-8 hover:border-blue-400 transition bg-slate-50/50 text-center relative">
                            <div v-if="lendoArquivo" class="absolute inset-0 bg-white/90 backdrop-blur-xs flex flex-col items-center justify-center rounded-2xl z-10 p-4">
                                <div class="w-9 h-9 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mb-3"></div>
                                <p class="text-xs font-bold text-blue-600 animate-pulse">Lendo prescrição e mapeando medicamentos...</p>
                            </div>
                            <div class="space-y-2">
                                <i class="pi pi-cloud-upload text-3xl text-slate-400"></i>
                                <div class="text-sm text-slate-600">
                                    <label class="relative cursor-pointer font-bold text-blue-600 hover:text-blue-500">
                                        <span>Clique para anexar a receita</span>
                                        <input type="file" class="sr-only" @change="handleFileUpload" accept=".pdf,.png,.jpg,.jpeg" />
                                    </label>
                                </div>
                                <p class="text-[11px] text-slate-400">PDF, PNG ou JPG (Máx. 10MB)</p>
                                <div v-if="form.arquivo" class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 text-xs px-3 py-1.5 rounded-lg font-semibold mt-2 border border-emerald-200">
                                    <i class="pi pi-file"></i> {{ form.arquivo.name }}
                                </div>
                            </div>
                        </div>
                        <div v-if="erroIA" class="bg-red-50 border border-red-200 text-red-700 text-xs p-3 rounded-xl">
                            {{ erroIA }}
                        </div>
                    </div>

                    <div v-if="modoAbas === 'manual' || iaSucesso" class="space-y-4 pt-4 border-t border-slate-100 animate-fade-in">

                        <div v-if="iaSucesso && modoAbas === 'ia'" class="bg-blue-50 border border-blue-100 p-4 rounded-xl text-blue-800 text-xs font-semibold flex items-center gap-2 mb-4">
                            <i class="pi pi-sparkles text-blue-500 animate-pulse"></i>
                            <span>Sucesso! Os dados estruturados da receita foram preenchidos abaixo.</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1 uppercase tracking-wider">Médico Responsável *</label>
                                <input v-model="form.medico" type="text" placeholder="Ex: Dr. Roberto" class="w-full rounded-xl border-slate-200 text-sm focus:ring-blue-500" required />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1 uppercase tracking-wider">Especialidade *</label>
                                <input v-model="form.especialidade" type="text" placeholder="Ex: Pediatria" class="w-full rounded-xl border-slate-200 text-sm focus:ring-blue-500" required />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1 uppercase tracking-wider">Data de Emissão *</label>
                                <input v-model="form.data_emissao" type="date" class="w-full rounded-xl border-slate-200 text-sm" required />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1 uppercase tracking-wider">Válida Até *</label>
                                <input v-model="form.data_validade" type="date" class="w-full rounded-xl border-slate-200 text-sm" required />
                            </div>
                        </div>

                        <div class="space-y-3 pt-4">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Medicamentos Catalogados</label>
                                <button v-if="modoAbas === 'manual'" type="button" @click="adicionarMedicamentoManual" class="text-xs font-bold text-blue-600 border-none bg-transparent cursor-pointer hover:underline">
                                    + Adicionar Remédio
                                </button>
                            </div>

                            <div v-for="(med, index) in form.medicamentos" :key="index" class="p-4 bg-slate-50 border border-slate-200 rounded-2xl space-y-3 relative">
                                <button v-if="form.medicamentos.length > 1 && modoAbas === 'manual'" type="button" @click="removerMedicamentoManual(index)" class="absolute top-2 right-2 text-red-500 bg-transparent border-none cursor-pointer">
                                    <i class="pi pi-times"></i>
                                </button>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <input v-model="med.nome" type="text" placeholder="Nome do Medicamento (Ex: Dipirona)" class="rounded-xl text-xs border-slate-200" required :disabled="modoAbas === 'ia'" />
                                    <input v-model="med.dosagem" type="text" placeholder="Dosagem (Ex: 500mg)" class="rounded-xl text-xs border-slate-200" required :disabled="modoAbas === 'ia'" />
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <input v-model="med.frequencia" type="text" placeholder="Frequência (Ex: De 6 em 6 horas)" class="rounded-xl text-xs border-slate-200" required :disabled="modoAbas === 'ia'" />
                                    <input v-model="med.duracao" type="text" placeholder="Duração (Ex: 5 dias)" class="rounded-xl text-xs border-slate-200" required :disabled="modoAbas === 'ia'" />
                                </div>
                            </div>
                        </div>

                        <div v-if="modoAbas === 'manual'" class="pt-2">
                            <label class="block text-xs font-bold text-slate-600 mb-2 uppercase tracking-wider">Anexar Comprovante Físico (Obrigatório) *</label>
                            <input type="file" @change="handleFileUpload" accept=".pdf,.png,.jpg,.jpeg" class="text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" required />
                        </div>

                        <div class="pt-4">
                            <button type="submit" :disabled="form.processing" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3.5 px-4 rounded-xl text-xs font-bold shadow-xs border-none cursor-pointer flex items-center justify-center gap-2">
                                <i v-if="form.processing" class="pi pi-spin pi-spinner"></i>
                                {{ form.processing ? 'Sincronizando com o MedCare...' : 'Salvar Prescrição no Prontuário' }}
                            </button>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
