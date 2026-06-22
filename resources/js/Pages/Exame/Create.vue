<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';

// Estado das abas: 'ia' ou 'manual'
const modoAbas = ref('ia');
const lendoArquivo = ref(false);
const erroIA = ref(null);
const iaSucesso = ref(false);

const form = useForm({
    modo_cadastro: 'ia',
    nome: '',
    tipo: '',
    laboratorio: '',
    data_realizacao: '',
    arquivo: null,
});

// Altera a aba e limpa estados anteriores para não confundir o usuário
const alternarAba = (aba) => {
    modoAbas.value = aba;
    form.modo_cadastro = aba;
    erroIA.value = null;

    if (aba === 'manual') {
        iaSucesso.value = false;
        form.reset('nome', 'tipo', 'laboratorio', 'data_realizacao');
    }
};

// Captura o arquivo selecionado
const handleFileUpload = (event) => {
    const arquivoSelecionado = event.target.files[0];
    if (!arquivoSelecionado) return;

    form.arquivo = arquivoSelecionado;
    erroIA.value = null;
    iaSucesso.value = false; // Reseta o sucesso caso mude de arquivo
};

// Disparado manualmente pelo botão de análise da IA
const iniciarAnaliseIA = () => {
    if (form.arquivo) {
        executarLeituraIA(form.arquivo);
    }
};

const executarLeituraIA = async (arquivo) => {
    lendoArquivo.value = true;
    erroIA.value = null;
    iaSucesso.value = false;

    const formData = new FormData();
    formData.append('arquivo', arquivo);

    try {
        const response = await axios.post(route('exames.analisar-ia'), formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });

        if (response.data && response.data.success) {
            const extraido = response.data.dados;

            // Preenche os campos do formulário reativamente
            form.nome = extraido.nome || '';
            form.tipo = extraido.tipo || 'Outros';
            form.laboratorio = extraido.laboratorio || 'Não informado';
            form.data_realizacao = extraido.data_realizacao || '';

            iaSucesso.value = true;
        } else {
            throw new Error('A resposta do servidor não retornou sucesso.');
        }
    } catch (error) {
        console.error("Erro na requisição da IA:", error);

        const mensagemErro = error.response?.data?.message || "Não fomos capazes de ler este documento automaticamente.";
        erroIA.value = `${mensagemErro} Por favor, insira os dados manualmente mudando de aba.`;

        // Força a mudança para digitação manual para não travar a experiência do usuário
        alternarAba('manual');
    } finally {
        lendoArquivo.value = false;
    }
};

const submitForm = () => {
    form.post(route('exames.store'), {
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            iaSucesso.value = false;
        },
    });
};
</script>

<template>
    <AppLayout title="Anexar Exame">
        <div class="w-full max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8 text-left">

            <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">

                <div class="p-6 sm:p-8 border-b border-slate-100 bg-slate-50/50">
                    <h1 class="text-xl lg:text-2xl font-black text-slate-800 tracking-tight">Inserir Novo Exame</h1>
                    <p class="text-xs text-slate-500 mt-1">Escolha o método de entrada para atualizar seu prontuário digital.</p>
                </div>

                <div class="px-6 sm:px-8 pt-6">
                    <div class="flex bg-slate-100 p-1 rounded-2xl max-w-sm border border-slate-200">
                        <button
                            type="button"
                            @click="alternarAba('ia')"
                            :class="['w-full py-2.5 text-xs font-bold rounded-xl border-none cursor-pointer transition-all duration-200 flex items-center justify-center gap-2',
                                    modoAbas === 'ia' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 bg-transparent hover:text-slate-800']"
                        >
                            <i class="pi pi-sparkles"></i> Inteligência Artificial
                        </button>
                        <button
                            type="button"
                            @click="alternarAba('manual')"
                            :class="['w-full py-2.5 text-xs font-bold rounded-xl border-none cursor-pointer transition-all duration-200 flex items-center justify-center gap-2',
                                    modoAbas === 'manual' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 bg-transparent hover:text-slate-800']"
                        >
                            <i class="pi pi-pencil"></i> Digitação Manual
                        </button>
                    </div>
                </div>

                <form @submit.prevent="submitForm" class="p-6 sm:p-8 space-y-6">

                    <div v-if="modoAbas === 'ia'" class="space-y-4">
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider">Carregar Laudo Médico *</label>

                        <div class="border-2 border-slate-300 border-dashed rounded-2xl p-8 hover:border-blue-400 transition bg-slate-50/50 text-center relative">

                            <div v-if="lendoArquivo" class="absolute inset-0 bg-white/90 backdrop-blur-xs flex flex-col items-center justify-center rounded-2xl z-10 p-4">
                                <div class="w-9 h-9 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mb-3"></div>
                                <p class="text-xs font-bold text-blue-600 animate-pulse">A IA do MedCare está processando e preenchendo os dados...</p>
                            </div>

                            <div class="space-y-2">
                                <i class="pi pi-cloud-upload text-3xl text-slate-400"></i>
                                <div class="text-sm text-slate-600">
                                    <label class="relative cursor-pointer font-bold text-blue-600 hover:text-blue-500">
                                        <span>Clique para selecionar o arquivo</span>
                                        <input type="file" class="sr-only" @change="handleFileUpload" accept=".pdf,.png,.jpg,.jpeg" />
                                    </label>
                                </div>
                                <p class="text-[11px] text-slate-400">Formatos aceitos: PDF, PNG ou JPG (Máx. 10MB)</p>

                                <div v-if="form.arquivo" class="mt-4 flex flex-col items-center justify-center gap-3">
                                    <div class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 text-xs px-3 py-1.5 rounded-lg font-semibold border border-emerald-200">
                                        <i class="pi pi-file text-[10px]"></i> {{ form.arquivo.name }}
                                    </div>

                                    <button
                                        v-if="!iaSucesso"
                                        type="button"
                                        @click="iniciarAnaliseIA"
                                        :disabled="lendoArquivo"
                                        class="bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 px-5 rounded-xl text-xs font-bold shadow-xs transition duration-200 border-none cursor-pointer flex items-center justify-center gap-2"
                                    >
                                        <i class="pi pi-sparkles"></i> Analisar com Inteligência Artificial
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div v-if="erroIA" class="bg-red-50 border border-red-200 text-red-700 text-xs p-3 rounded-xl flex items-start gap-2">
                            <i class="pi pi-exclamation-circle mt-0.5"></i>
                            <span>{{ erroIA }}</span>
                        </div>
                    </div>

                    <div v-if="modoAbas === 'manual' || iaSucesso" class="space-y-4 pt-4 border-t border-slate-100 animate-fade-in">

                        <div v-if="iaSucesso && modoAbas === 'ia'" class="bg-blue-50 border border-blue-100 p-4 rounded-xl text-blue-800 text-xs font-semibold flex items-center gap-2">
                            <i class="pi pi-sparkles text-blue-500 animate-pulse text-sm"></i>
                            <span>Concluído! Verifique as informações extraídas abaixo antes de salvar.</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1 uppercase tracking-wider">Nome do Exame *</label>
                            <input v-model="form.nome" type="text" placeholder="Ex: Hemograma Completo"
                                   class="w-full rounded-xl border-slate-200 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500" required />
                            <div v-if="form.errors.nome" class="text-red-500 text-xs mt-1">{{ form.errors.nome }}</div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1 uppercase tracking-wider">Tipo / Categoria *</label>
                                <select v-model="form.tipo" class="w-full rounded-xl border-slate-200 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500" required>
                                    <option value="">Selecione uma categoria</option>
                                    <option value="Sangue">Exame de Sangue</option>
                                    <option value="Imagem">Exame de Imagem</option>
                                    <option value="Urina">Exame de Urina</option>
                                    <option value="Outros">Outros Diagnósticos</option>
                                </select>
                                <div v-if="form.errors.tipo" class="text-red-500 text-xs mt-1">{{ form.errors.tipo }}</div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1 uppercase tracking-wider">Data de Realização *</label>
                                <input v-model="form.data_realizacao" type="date"
                                       class="w-full rounded-xl border-slate-200 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500" required />
                                <div v-if="form.errors.data_realizacao" class="text-red-500 text-xs mt-1">{{ form.errors.data_realizacao }}</div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1 uppercase tracking-wider">Laboratório / Clínica</label>
                            <input v-model="form.laboratorio" type="text" placeholder="Ex: Laboratório Sabin"
                                   class="w-full rounded-xl border-slate-200 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500" />
                            <div v-if="form.errors.laboratorio" class="text-red-500 text-xs mt-1">{{ form.errors.laboratorio }}</div>
                        </div>

                        <div class="pt-4">
                            <button type="submit" :disabled="form.processing"
                                    class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3.5 px-4 rounded-xl text-xs font-bold shadow-xs transition disabled:opacity-50 border-none cursor-pointer flex items-center justify-center gap-2">
                                <i v-if="form.processing" class="pi pi-spin pi-spinner"></i>
                                {{ form.processing ? 'Gravando dados na nuvem...' : 'Salvar Registro na Carteira' }}
                            </button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </AppLayout>
</template>
