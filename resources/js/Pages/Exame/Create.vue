<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    nome: '',
    tipo: '',
    laboratorio: '',
    data_realizacao: '',
    arquivo: null,
});

// Método para lidar com o envio do formulário com arquivos binários
const submitForm = () => {
    form.post(route('exames.store'), {
        forceFormData: true, // Força o envio como multipart/form-data por conta do upload
        onSuccess: () => form.reset(),
    });
};

// Captura o arquivo selecionado no input
const handleFileUpload = (event) => {
    form.arquivo = event.target.files[0];
};
</script>

<template>
    <AppLayout title="Anexar Exame">
        <div class="w-full max-w-5xl mx-auto py-6 px-4 sm:px-6 lg:px-8">

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-6 sm:p-8 lg:p-10 border-b border-gray-100 bg-gray-50/50">
                    <h1 class="text-xl lg:text-2xl font-bold text-gray-800">Inserir Novo Exame</h1>
                    <p class="text-xs text-gray-500 mt-1">Preencha as informações do laudo para salvar na sua carteira digital.</p>
                </div>

                <form @submit.prevent="submitForm" class="p-6 sm:p-8 lg:p-10 space-y-6 lg:space-y-8">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome do Exame *</label>
                        <input v-model="form.nome" type="text" placeholder="Ex: Hemograma Completo, Ultrassom"
                               class="w-full rounded-xl border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500" />
                        <div v-if="form.errors.nome" class="text-red-500 text-xs mt-1">{{ form.errors.nome }}</div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 lg:gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Exame *</label>
                            <select v-model="form.tipo" class="w-full rounded-xl border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Selecione uma opção</option>
                                <option value="Sangue">Exame de Sangue</option>
                                <option value="Imagem">Exame de Imagem (Raio-X, Tomografia, etc)</option>
                                <option value="Urina">Exame de Urina</option>
                                <option value="Outros">Outros</option>
                            </select>
                            <div v-if="form.errors.tipo" class="text-red-500 text-xs mt-1">{{ form.errors.tipo }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Data de Realização *</label>
                            <input v-model="form.data_realizacao" type="date"
                                   class="w-full rounded-xl border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500" />
                            <div v-if="form.errors.data_realizacao" class="text-red-500 text-xs mt-1">{{ form.errors.data_realizacao }}</div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Laboratório / Clínica</label>
                        <input v-model="form.laboratorio" type="text" placeholder="Ex: CliniCenter, Sabin"
                               class="w-full rounded-xl border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500" />
                        <div v-if="form.errors.laboratorio" class="text-red-500 text-xs mt-1">{{ form.errors.laboratorio }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Documento do Exame (PDF ou Imagem) *</label>
                        <div class="mt-1 flex justify-center px-6 lg:px-10 pt-5 lg:pt-8 pb-6 lg:pb-8 border-2 border-gray-300 border-dashed rounded-xl hover:border-blue-400 transition-colors">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                    <path d="M28 8H12a4 4 0 00-4 4v20a4 4 0 004 4h20a4 4 0 004-4V20m-8-12v8h8M14 22L22 14l8 8M22 14v22" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600 justify-center">
                                    <label class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500">
                                        <span>Escolha o arquivo</span>
                                        <input type="file" class="sr-only" @change="handleFileUpload" accept=".pdf,.png,.jpg,.jpeg" />
                                    </label>
                                </div>
                                <p class="text-xs text-gray-500">PDF, PNG, JPG até 10MB</p>
                                <p v-if="form.arquivo" class="text-xs font-semibold text-green-600 mt-2">
                                    ✓ Selecionado: {{ form.arquivo.name }}
                                </p>
                            </div>
                        </div>
                        <div v-if="form.errors.arquivo" class="text-red-500 text-xs mt-1">{{ form.errors.arquivo }}</div>
                    </div>

                    <div class="pt-4">
                        <button type="submit" :disabled="form.processing"
                                class="w-full bg-blue-600 text-white py-3 px-4 rounded-xl text-sm font-bold shadow-sm hover:bg-blue-700 transition-colors disabled:opacity-50">
                            {{ form.processing ? 'Enviando documento...' : 'Salvar na Carteira Digital' }}
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </AppLayout>
</template>
