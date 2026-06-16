<script setup>
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    ultimoExamePendente: Object,
    paciente: Object
});

// Controle do Modal de Ativação do WhatsApp
const useWhatsappModal = ref(false);
const whatsappNumero = ref('');
const statusIntegracao = ref('disponivel'); // disponivel, processando, integrado

const ativarIntegracao = () => {
    if (!whatsappNumero.value) return;
    statusIntegracao.value = 'processando';
    setTimeout(() => {
        statusIntegracao.value = 'integrado';
    }, 2000);
};
</script>

<template>
    <AppLayout title="Meu MedCare">
        <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 bg-slate-50/50 min-h-screen pb-24">

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8 bg-white p-6 rounded-3xl border border-slate-100 shadow-xs">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-800 tracking-tight">
                        Olá, {{ $page.props.auth.user.name.split(' ')[0] }}
                    </h1>
                    <p class="text-slate-500 text-sm sm:text-base mt-0.5">Como está a sua saúde hoje?</p>
                </div>
                <div class="flex items-center gap-2 bg-emerald-50 text-emerald-700 px-4 py-2 rounded-2xl text-xs font-bold self-start sm:self-center border border-emerald-100">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Monitoramento IA Inteligente
                </div>
            </div>

            <div v-if="statusIntegracao !== 'integrado'" class="mb-8 bg-gradient-to-br from-emerald-600 to-teal-700 rounded-3xl p-6 text-white shadow-sm relative overflow-hidden text-left">
                <div class="relative z-10 max-w-2xl">
                    <span class="bg-white/20 text-white text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-1 rounded-md backdrop-blur-md">
                        Ecossistema Integrado
                    </span>
                    <h3 class="text-xl font-extrabold mt-3 tracking-tight">Traga o MedCare para o seu WhatsApp</h3>
                    <p class="text-emerald-100 text-sm mt-2 leading-relaxed">
                        Envie mensagens, fotos de exames, receitas ou registre sintomas diretamente pelo chat. Nossa IA processa as informações e atualiza seus módulos automaticamente.
                    </p>
                    <button
                        @click="useWhatsappModal = true"
                        class="mt-5 bg-white text-emerald-700 hover:bg-emerald-50 px-5 py-2.5 rounded-2xl text-sm font-bold tracking-wide shadow-xs transition flex items-center gap-2 border-none cursor-pointer"
                    >
                        <i class="pi pi-whatsapp text-lg"></i>
                        Vincular meu WhatsApp
                    </button>
                </div>
                <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-emerald-500/20 rounded-full blur-xl pointer-events-none"></div>
            </div>

            <div v-else class="mb-8 bg-white border border-emerald-100 rounded-3xl p-4 flex items-center justify-between shadow-xs text-left">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600">
                        <i class="pi pi-check-circle text-xl"></i>
                    </div>
                    <div>
                        <p class="font-bold text-slate-800 text-sm">WhatsApp Vinculado!</p>
                        <p class="text-xs text-slate-400">Pronto para receber comandos de texto, áudio ou arquivos.</p>
                    </div>
                </div>
                <button @click="statusIntegracao = 'disponivel'" class="text-xs font-bold text-slate-400 hover:text-rose-500 bg-transparent border-none cursor-pointer">Desconectar</button>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-5 text-left">

                <Link :href="route('jornada-inteligente.index')" class="col-span-2 md:col-span-2 lg:col-span-2 bg-gradient-to-br from-indigo-900 via-indigo-950 to-slate-900 p-6 rounded-3xl shadow-xs hover:shadow-md transition group flex flex-col justify-between min-h-[160px] text-white">
                    <div class="flex justify-between items-start">
                        <div class="w-12 h-12 bg-white/10 group-hover:bg-white/20 rounded-2xl flex items-center justify-center transition">
                            <i class="pi pi-sparkles text-xl text-indigo-300"></i>
                        </div>
                        <span class="bg-indigo-500/30 text-indigo-200 font-extrabold text-[10px] px-2.5 py-1 rounded-md uppercase tracking-wider backdrop-blur-xs">
                            Central de IA
                        </span>
                    </div>
                    <div class="mt-4">
                        <span class="block text-base font-bold text-white">Jornada Inteligente</span>
                        <p class="text-xs text-indigo-200 mt-1">Acompanhamento preditivo completo. Monitore sintomas e compile resumos estruturados pela IA para apresentar em suas consultas.</p>
                    </div>
                </Link>

                <Link :href="route('lembretes.index')" class="bg-white p-6 rounded-3xl shadow-xs border border-slate-100 hover:border-emerald-100 hover:shadow-md transition group flex flex-col justify-between min-h-[160px]">
                    <div class="flex justify-between items-start">
                        <div class="w-12 h-12 bg-emerald-50 group-hover:bg-emerald-100 rounded-2xl flex items-center justify-center transition">
                            <i class="pi pi-bell text-xl text-emerald-600"></i>
                        </div>
                    </div>
                    <div class="mt-4">
                        <span class="block text-base font-bold text-slate-800">Lembretes & Consultas</span>
                        <p class="text-xs text-slate-400 mt-0.5">Gerencie os horários dos seus medicamentos, consultas e retornos médicos.</p>
                    </div>
                </Link>

                <Link :href="route('exames.index')" class="bg-white p-6 rounded-3xl shadow-xs border border-slate-100 hover:border-blue-100 hover:shadow-md transition group flex flex-col justify-between min-h-[160px]">
                    <div class="w-12 h-12 bg-blue-50 group-hover:bg-blue-100 rounded-2xl flex items-center justify-center transition">
                        <i class="pi pi-file-pdf text-xl text-blue-600"></i>
                    </div>
                    <div class="mt-4">
                        <span class="block text-base font-bold text-slate-800">Exames</span>
                        <p class="text-xs text-slate-400 mt-0.5">Armazene e organize seus laudos médicos, exames de imagem e laboratoriais.</p>
                    </div>
                </Link>

                <Link v-if="paciente" :href="route('vacinacoes.index', paciente.id)" class="bg-white p-6 rounded-3xl shadow-xs border border-slate-100 hover:border-green-100 hover:shadow-md transition group flex flex-col justify-between min-h-[160px]">
                    <div class="w-12 h-12 bg-green-50 group-hover:bg-green-100 rounded-2xl flex items-center justify-center transition">
                        <i class="pi pi-shield text-xl text-green-600"></i>
                    </div>
                    <div class="mt-4">
                        <span class="block text-base font-bold text-slate-800">Vacinas</span>
                        <p class="text-xs text-slate-400 mt-0.5">Histórico completo de imunização e controle do calendário vacinal.</p>
                    </div>
                </Link>

                <Link :href="route('receitas.index', paciente.id)" class="bg-white p-6 rounded-3xl shadow-xs border border-slate-100 hover:border-purple-100 hover:shadow-md transition group flex flex-col justify-between min-h-[160px]">
                    <div class="w-12 h-12 bg-purple-50 group-hover:bg-purple-100 rounded-2xl flex items-center justify-center transition">
                        <i class="pi pi-paperclip text-xl text-purple-600"></i>
                    </div>
                    <div class="mt-4">
                        <span class="block text-base font-bold text-slate-800">Receitas</span>
                        <p class="text-xs text-slate-400 mt-0.5">Centralize prescrições ativas, dosagens de medicamentos e orientações.</p>
                    </div>
                </Link>

                <Link :href="route('historico.ps', paciente.id)" class="bg-white p-6 rounded-3xl shadow-xs border border-slate-100 hover:border-red-100 hover:shadow-md transition group flex flex-col justify-between min-h-[160px]">
                    <div class="w-12 h-12 bg-red-50 group-hover:bg-red-100 rounded-2xl flex items-center justify-center transition">
                        <i class="pi pi-heart-fill text-xl text-red-600"></i>
                    </div>
                    <div class="mt-4">
                        <span class="block text-base font-bold text-slate-800">Histórico Clínico</span>
                        <p class="text-xs text-slate-400 mt-0.5">Registro cronológico de sintomas e passagens por unidades de saúde.</p>
                    </div>
                </Link>

            </div>

            <div class="fixed bottom-6 right-6 z-50">
                <Link
                    href="/whatsapp-simulador"
                    class="w-14 h-14 bg-emerald-500 text-white rounded-full flex items-center justify-center shadow-lg hover:bg-emerald-600 transition hover:scale-105 active:scale-95 group relative border-none cursor-pointer"
                >
                    <i class="pi pi-whatsapp text-2xl"></i>
                    <span class="absolute right-16 bg-slate-900 text-white text-[11px] font-bold px-3 py-1.5 rounded-xl shadow-md opacity-0 group-hover:opacity-100 transition whitespace-nowrap pointer-events-none">
                        Simulador WhatsApp IA
                    </span>
                </Link>
            </div>

            <div v-if="useWhatsappModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 z-50">
                <div class="bg-white rounded-3xl p-6 max-w-sm w-full shadow-xl border border-slate-100 text-left">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="text-lg font-black text-slate-800">Ativar WhatsApp</h4>
                        <button @click="useWhatsappModal = false" class="text-slate-400 hover:text-slate-600 bg-transparent border-none text-base cursor-pointer">
                            <i class="pi pi-times"></i>
                        </button>
                    </div>

                    <div v-if="statusIntegracao === 'disponivel'">
                        <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                            Insira seu número com o DDD para vincular seu perfil à nossa inteligência conversacional.
                        </p>
                        <div class="mb-4">
                            <label class="block text-xs font-bold text-slate-600 mb-1 uppercase tracking-wider">Número do Celular</label>
                            <input
                                v-model="whatsappNumero"
                                type="text"
                                placeholder="(92) 99999-9999"
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:outline-hidden focus:border-emerald-500 text-slate-800"
                            />
                        </div>
                        <button
                            @click="ativarIntegracao"
                            class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-3 rounded-xl text-sm transition border-none cursor-pointer"
                        >
                            Confirmar Vínculo
                        </button>
                    </div>

                    <div v-if="statusIntegracao === 'processando'" class="py-8 flex flex-col items-center justify-center text-center">
                        <div class="w-10 h-10 border-4 border-emerald-500 border-t-transparent rounded-full animate-spin mb-4"></div>
                        <p class="text-sm font-bold text-slate-700">Conectando Webhooks...</p>
                    </div>

                    <div v-if="statusIntegracao === 'integrado'" class="py-4 text-center">
                        <div class="w-12 h-12 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="pi pi-check text-xl font-bold"></i>
                        </div>
                        <p class="text-sm font-bold text-slate-800">Sincronização Pronta!</p>
                        <p class="text-xs text-slate-400 mt-1 mb-4">O fluxo inteligente agora está mapeado para o seu perfil.</p>
                        <button
                            @click="useWhatsappModal = false"
                            class="w-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-2.5 rounded-xl text-xs transition border-none cursor-pointer"
                        >
                            Fechar Janela
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
