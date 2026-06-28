<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import Button from 'primevue/button';
import { Link } from '@inertiajs/vue3';

const scrollToFeatures = () => {
    document.getElementById('funcionalidades')?.scrollIntoView({
        behavior: 'smooth',
        block: 'start',
    });
};

const funcionalidades = [
    {
        icon: 'pi pi-id-card',
        titulo: 'Perfil de Saúde',
        descricao: 'Mantenha tipo sanguíneo, alergias, condições crônicas, medicamentos contínuos e informações importantes sempre atualizados.',
    },
    {
        icon: 'pi pi-sparkles',
        titulo: 'Jornada Inteligente',
        descricao: 'Registre sintomas e acontecimentos, gere um sumário clínico completo e baixe o documento em PDF para levar à consulta.',
    },
    {
        icon: 'pi pi-folder-open',
        titulo: 'Documentos organizados',
        descricao: 'Centralize exames, receitas, vacinas e registros do Histórico Clínico em módulos separados e fáceis de consultar.',
    },
    {
        icon: 'pi pi-bell',
        titulo: 'Central de Pendências',
        descricao: 'Acompanhe lembretes, próximas doses, prescrições próximas do vencimento e outras pendências importantes.',
    },
    {
        icon: 'pi pi-comments',
        titulo: 'Assistente conversacional',
        descricao: 'Use uma experiência de chat para cadastrar documentos, criar lembretes, consultar informações e gerar resumos.',
    },
    {
        icon: 'pi pi-file-pdf',
        titulo: 'Resumo pronto para consulta',
        descricao: 'Transforme os dados selecionados em um documento auxiliar para visualizar, baixar ou imprimir quando precisar.',
    },
];

const etapas = [
    {
        numero: '01',
        titulo: 'Cadastre seus dados',
        descricao: 'Preencha seu Perfil de Saúde e adicione exames, receitas, vacinas e registros clínicos.',
    },
    {
        numero: '02',
        titulo: 'Deixe o MedCare organizar',
        descricao: 'A plataforma separa as informações por módulo e ajuda a manter sua jornada atualizada.',
    },
    {
        numero: '03',
        titulo: 'Acompanhe e compartilhe',
        descricao: 'Consulte pendências e gere um sumário em PDF para apoiar sua conversa com o profissional de saúde.',
    },
];
</script>

<template>
    <PublicLayout>
        <main class="overflow-hidden bg-surface-0 text-surface-900">
            <section class="relative border-b border-surface-200 bg-gradient-to-b from-primary-50 via-white to-white">
                <div class="pointer-events-none absolute -left-28 top-20 h-72 w-72 rounded-full bg-primary-200/40 blur-3xl"></div>
                <div class="pointer-events-none absolute -right-20 top-10 h-80 w-80 rounded-full bg-secondary-200/40 blur-3xl"></div>

                <div class="relative mx-auto grid max-w-7xl items-center gap-14 px-6 py-16 lg:grid-cols-[1.05fr_0.95fr] lg:px-8 lg:py-24">
                    <div>
                        <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-primary-200 bg-white/80 px-4 py-2 text-sm font-semibold text-primary-700 shadow-sm backdrop-blur">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-primary-100">
                                <i class="pi pi-heart-fill text-primary-600"></i>
                            </span>
                            Sua jornada de saúde, organizada por você
                        </div>

                        <h1 class="max-w-3xl text-4xl font-extrabold leading-tight tracking-tight text-surface-950 sm:text-5xl lg:text-6xl">
                            Todas as suas informações de saúde
                            <span class="block bg-gradient-to-r from-primary-600 to-secondary-600 bg-clip-text text-transparent">
                                em um só lugar
                            </span>
                        </h1>

                        <p class="mt-6 max-w-2xl text-lg leading-8 text-surface-600">
                            O MedCare Digital ajuda você a organizar documentos, acompanhar pendências, registrar sua jornada e preparar informações úteis para consultas com apoio de inteligência artificial.
                        </p>

                        <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center">
                            <Link :href="route('register')" class="w-full sm:w-auto">
                                <Button
                                    label="Criar minha conta"
                                    icon="pi pi-arrow-right"
                                    iconPos="right"
                                    class="w-full !rounded-xl !px-6 !py-3 !font-bold shadow-lg shadow-primary-200 sm:w-auto"
                                />
                            </Link>

                            <Button
                                label="Conhecer funcionalidades"
                                icon="pi pi-play-circle"
                                outlined
                                class="w-full !rounded-xl !px-6 !py-3 !font-bold sm:w-auto"
                                @click="scrollToFeatures"
                            />
                        </div>

                        <div class="mt-8 flex flex-wrap gap-x-6 gap-y-3 text-sm font-medium text-surface-600">
                            <span class="inline-flex items-center gap-2">
                                <i class="pi pi-check-circle text-secondary-600"></i>
                                Dados separados por usuário
                            </span>
                            <span class="inline-flex items-center gap-2">
                                <i class="pi pi-check-circle text-secondary-600"></i>
                                Resumos clínicos em PDF
                            </span>
                            <span class="inline-flex items-center gap-2">
                                <i class="pi pi-check-circle text-secondary-600"></i>
                                Controle nas mãos do paciente
                            </span>
                        </div>
                    </div>

                    <div class="relative mx-auto w-full max-w-xl">
                        <div class="absolute -inset-5 rounded-[2rem] bg-gradient-to-br from-primary-200/60 to-secondary-200/60 blur-2xl"></div>

                        <div class="relative overflow-hidden rounded-[2rem] border border-white/80 bg-white p-4 shadow-2xl shadow-primary-950/10 sm:p-6">
                            <div class="mb-5 flex items-center justify-between border-b border-surface-100 pb-4">
                                <div>
                                    <p class="text-sm font-semibold text-primary-600">MedCare Digital</p>
                                    <h2 class="mt-1 text-xl font-bold text-surface-900">Visão geral da sua saúde</h2>
                                </div>
                                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-primary-100 text-primary-700">
                                    <i class="pi pi-heart-fill text-lg"></i>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div class="rounded-2xl bg-primary-50 p-4">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary-100 text-primary-700">
                                            <i class="pi pi-file"></i>
                                        </span>
                                        <div>
                                            <p class="text-xs font-semibold uppercase tracking-wide text-surface-500">Documentos</p>
                                            <p class="mt-1 font-bold text-surface-900">Organizados</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-2xl bg-secondary-50 p-4">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-secondary-100 text-secondary-700">
                                            <i class="pi pi-bell"></i>
                                        </span>
                                        <div>
                                            <p class="text-xs font-semibold uppercase tracking-wide text-surface-500">Pendências</p>
                                            <p class="mt-1 font-bold text-surface-900">Em destaque</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 rounded-2xl border border-surface-200 p-4">
                                <div class="flex items-start gap-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-tertiary-100 text-tertiary-700">
                                        <i class="pi pi-sparkles"></i>
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between gap-3">
                                            <h3 class="font-bold text-surface-900">Jornada Inteligente</h3>
                                            <span class="rounded-full bg-tertiary-50 px-3 py-1 text-xs font-bold text-tertiary-700">IA</span>
                                        </div>
                                        <p class="mt-2 text-sm leading-6 text-surface-600">
                                            Gere um resumo com perfil, relatos, exames, receitas, vacinas e histórico clínico.
                                        </p>
                                        <div class="mt-3 flex items-center gap-2 text-sm font-semibold text-primary-600">
                                            <i class="pi pi-file-pdf"></i>
                                            Pronto para visualizar e baixar
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 rounded-2xl bg-surface-950 p-4 text-white">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10">
                                        <i class="pi pi-comments"></i>
                                    </span>
                                    <div>
                                        <p class="font-bold">Assistente MedCare</p>
                                        <p class="mt-1 text-sm text-surface-300">Uma experiência de chat para ajudar a organizar seus dados.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="funcionalidades" class="scroll-mt-24 bg-white px-6 py-20 lg:px-8">
                <div class="mx-auto max-w-7xl">
                    <div class="mx-auto max-w-3xl text-center">
                        <span class="text-sm font-bold uppercase tracking-[0.2em] text-primary-600">Ecossistema MedCare</span>
                        <h2 class="mt-4 text-3xl font-extrabold tracking-tight text-surface-950 sm:text-4xl">
                            Recursos pensados para a rotina do paciente
                        </h2>
                        <p class="mt-4 text-lg leading-8 text-surface-600">
                            Uma plataforma para reunir informações, acompanhar o que precisa de atenção e chegar mais preparado às consultas.
                        </p>
                    </div>

                    <div class="mt-14 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                        <article
                            v-for="item in funcionalidades"
                            :key="item.titulo"
                            class="group rounded-3xl border border-surface-200 bg-white p-6 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-primary-200 hover:shadow-xl hover:shadow-primary-950/5"
                        >
                            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-primary-50 text-primary-600 transition group-hover:bg-primary-600 group-hover:text-white">
                                <i :class="item.icon" class="text-xl"></i>
                            </span>
                            <h3 class="mt-5 text-xl font-bold text-surface-900">{{ item.titulo }}</h3>
                            <p class="mt-3 leading-7 text-surface-600">{{ item.descricao }}</p>
                        </article>
                    </div>
                </div>
            </section>

            <section class="border-y border-surface-200 bg-surface-50 px-6 py-20 lg:px-8">
                <div class="mx-auto max-w-7xl">
                    <div class="grid items-center gap-12 lg:grid-cols-[0.9fr_1.1fr]">
                        <div>
                            <span class="text-sm font-bold uppercase tracking-[0.2em] text-secondary-700">Simples de usar</span>
                            <h2 class="mt-4 text-3xl font-extrabold tracking-tight text-surface-950 sm:text-4xl">
                                Da informação solta ao cuidado organizado
                            </h2>
                            <p class="mt-5 text-lg leading-8 text-surface-600">
                                O MedCare organiza o que está espalhado em arquivos, conversas e anotações para que você encontre o que precisa com mais facilidade.
                            </p>

                            <div class="mt-8 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm leading-6 text-amber-900">
                                <div class="flex items-start gap-3">
                                    <i class="pi pi-info-circle mt-1"></i>
                                    <p>
                                        O MedCare é uma ferramenta de organização e apoio. Os resumos gerados não substituem avaliação, diagnóstico ou orientação de um profissional de saúde.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div
                                v-for="etapa in etapas"
                                :key="etapa.numero"
                                class="flex gap-5 rounded-3xl border border-surface-200 bg-white p-6 shadow-sm"
                            >
                                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-surface-950 text-sm font-extrabold text-white">
                                    {{ etapa.numero }}
                                </span>
                                <div>
                                    <h3 class="text-xl font-bold text-surface-900">{{ etapa.titulo }}</h3>
                                    <p class="mt-2 leading-7 text-surface-600">{{ etapa.descricao }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="px-6 py-20 lg:px-8">
                <div class="mx-auto max-w-7xl overflow-hidden rounded-[2rem] bg-gradient-to-br from-primary-700 via-primary-800 to-secondary-800 px-6 py-12 text-white shadow-2xl shadow-primary-950/20 sm:px-10 lg:px-14">
                    <div class="grid items-center gap-8 lg:grid-cols-[1fr_auto]">
                        <div>
                            <span class="inline-flex rounded-full bg-white/10 px-4 py-2 text-sm font-semibold text-primary-100">
                                Comece pela sua própria jornada
                            </span>
                            <h2 class="mt-5 max-w-3xl text-3xl font-extrabold tracking-tight sm:text-4xl">
                                Mais organização para cuidar melhor das suas informações de saúde
                            </h2>
                            <p class="mt-4 max-w-2xl text-lg leading-8 text-primary-100">
                                Crie sua conta, preencha seu Perfil de Saúde e tenha seus documentos, lembretes e resumos reunidos em um único ambiente.
                            </p>
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row lg:flex-col">
                            <Link :href="route('register')">
                                <Button
                                    label="Começar agora"
                                    icon="pi pi-arrow-right"
                                    iconPos="right"
                                    class="w-full !border-white !bg-white !px-7 !py-3 !font-bold !text-primary-800 hover:!bg-primary-50"
                                />
                            </Link>
                            <Link :href="route('login')">
                                <Button
                                    label="Já tenho uma conta"
                                    icon="pi pi-sign-in"
                                    outlined
                                    class="w-full !border-white/50 !px-7 !py-3 !font-bold !text-white hover:!bg-white/10"
                                />
                            </Link>
                        </div>
                    </div>
                </div>
            </section>

            <footer class="border-t border-surface-200 bg-surface-50 px-6 py-8 lg:px-8">
                <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 text-center text-sm text-surface-500 sm:flex-row sm:text-left">
                    <p>© 2026 MedCare Digital. Organização e apoio para a jornada do paciente.</p>
                    <div class="flex items-center gap-5">
                        <Link :href="route('login')" class="font-semibold text-surface-700 hover:text-primary-600">Entrar</Link>
                        <Link :href="route('register')" class="font-semibold text-surface-700 hover:text-primary-600">Criar conta</Link>
                    </div>
                </div>
            </footer>
        </main>
    </PublicLayout>
</template>
