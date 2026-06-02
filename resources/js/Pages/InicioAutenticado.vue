<script setup>
import LogoHorizontalGrande from '@/Components/Logos/LogoHorizontalGrande.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
defineProps({
    ultimoExamePendente: Object,
    paciente: Object
});

</script>

<template>
    <AppLayout title="Meu MedCare">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">

            <div class="mb-8">
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">
                    Olá, {{ $page.props.auth.user.name.split(' ')[0] }}
                </h1>
                <p class="text-gray-500 text-sm sm:text-base">Como está sua saúde hoje?</p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">

                <Link :href="route('exames.index')" class="flex flex-col items-center justify-center bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                    <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center mb-3">
                        <i class="pi pi-file-pdf text-2xl text-blue-600"></i>
                    </div>
                    <span class="text-sm font-semibold text-gray-700 text-center">Exames</span>
                </Link>

                <Link v-if="paciente" :href="route('vacinacoes.index', paciente.id)" class="flex flex-col items-center justify-center bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                    <div class="w-16 h-16 bg-green-50 rounded-full flex items-center justify-center mb-3">
                        <i class="pi pi-shield text-2xl text-green-600"></i>
                    </div>
                    <span class="text-sm font-semibold text-gray-700 text-center">Vacinas</span>
                </Link>

                <Link :href="route('guia.inicio')" class="flex flex-col items-center justify-center bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                    <div class="w-16 h-16 bg-orange-50 rounded-full flex items-center justify-center mb-3">
                        <i class="pi pi-map-marker text-2xl text-orange-600"></i>
                    </div>
                    <span class="text-sm font-semibold text-gray-700 text-center">Guia Médico</span>
                </Link>

                <Link :href="route('receitas.index', paciente.id)" class="flex flex-col items-center justify-center bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                    <div class="w-16 h-16 bg-purple-50 rounded-full flex items-center justify-center mb-3">
                        <i class="pi pi-paperclip text-2xl text-purple-600"></i>
                    </div>
                    <span class="text-sm font-semibold text-gray-700 text-center">Receitas</span>
                </Link>

                <Link :href="route('historico.ps', paciente.id)" class="flex flex-col items-center justify-center bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                    <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mb-3">
                        <i class="pi pi-heart-fill text-2xl text-red-600"></i>
                    </div>
                    <span class="text-sm font-semibold text-gray-700 text-center">Histórico PS</span>
                </Link>

                <Link :href="route('meu.plano', paciente.id)" class="flex flex-col items-center justify-center bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                    <div class="w-16 h-16 bg-teal-50 rounded-full flex items-center justify-center mb-3">
                        <i class="pi pi-id-card text-2xl text-teal-600"></i>
                    </div>
                    <span class="text-sm font-semibold text-gray-700 text-center">Meu Plano</span>
                </Link>
            </div>

            <div v-if="ultimoExamePendente" class="mt-8 bg-blue-600 rounded-3xl p-6 text-white shadow-lg overflow-hidden relative">
                <div class="relative z-10">
                    <h3 class="text-lg font-bold">Informação importante</h3>
                    <p class="text-blue-100 text-sm mt-2">
                        Você tem exame pendente de visualização realizado na clínica
                        <strong class="text-white">{{ ultimoExamePendente.laboratorio }}</strong>.
                    </p>

                    <Link :href="route('exames.show', ultimoExamePendente.id)"
                          class="mt-4 bg-white text-blue-600 px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider inline-block hover:bg-blue-50 transition-colors">
                        Ver Resultado
                    </Link>
                </div>
                <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-blue-500 rounded-full opacity-50"></div>
            </div>

        </div>
    </AppLayout>
</template>
