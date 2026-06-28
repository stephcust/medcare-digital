<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    ultimoExamePendente: Object,
    paciente: Object,
    resumoPendencias: {
        type: Object,
        default: () => ({ atrasadas: 0, hoje: 0, proximas: 0, automaticas: 0 })
    },
    pendenciasDestaque: {
        type: Array,
        default: () => []
    }
});
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

            <Link
                v-if="resumoPendencias.atrasadas || resumoPendencias.hoje || resumoPendencias.proximas || resumoPendencias.automaticas"
                :href="route('lembretes.index')"
                class="mb-8 block rounded-3xl border border-amber-200 bg-gradient-to-r from-amber-50 to-orange-50 p-5 text-left no-underline shadow-xs transition hover:border-amber-300"
            >
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                                <i class="pi pi-bell"></i>
                            </div>
                            <div>
                                <p class="text-sm font-black text-slate-800">Central de Pendências</p>
                                <p class="text-xs text-slate-500">O MedCare encontrou itens que merecem sua atenção.</p>
                            </div>
                        </div>

                        <div class="mt-3 flex flex-wrap gap-2 text-[11px] font-bold">
                            <span v-if="resumoPendencias.atrasadas" class="rounded-full bg-rose-100 px-2.5 py-1 text-rose-700">
                                {{ resumoPendencias.atrasadas }} atrasada(s)
                            </span>
                            <span v-if="resumoPendencias.hoje" class="rounded-full bg-amber-100 px-2.5 py-1 text-amber-700">
                                {{ resumoPendencias.hoje }} para hoje
                            </span>
                            <span v-if="resumoPendencias.proximas" class="rounded-full bg-blue-100 px-2.5 py-1 text-blue-700">
                                {{ resumoPendencias.proximas }} próxima(s)
                            </span>
                            <span v-if="resumoPendencias.automaticas" class="rounded-full bg-indigo-100 px-2.5 py-1 text-indigo-700">
                                {{ resumoPendencias.automaticas }} alerta(s) automático(s)
                            </span>
                        </div>
                    </div>

                    <span class="text-xs font-black text-amber-800">Ver pendências →</span>
                </div>

                <div v-if="pendenciasDestaque.length" class="mt-4 border-t border-amber-200/70 pt-3">
                    <p v-for="pendencia in pendenciasDestaque.slice(0, 3)" :key="pendencia.id" class="mt-1 text-xs text-slate-600">
                        • {{ pendencia.titulo }}
                    </p>
                </div>
            </Link>

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

        </div>
    </AppLayout>
</template>
