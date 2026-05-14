<script setup>
import LogoPMMHorizontalGrande from '@/Components/Logos/LogoPMMHorizontalGrande.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
</script>

<template>
    <AppLayout title="Início">
        <div class="max-w-7xl mx-auto">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">

                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                    <div class="flex justify-center">
                        <LogoPMMHorizontalGrande class="dark" />
                    </div>

                    <h1 class="mt-8 text-2xl font-medium text-gray-900">
                        Bem-vindo à sua aplicação Jetstream: Laravel 11 + Vue 3!
                    </h1>

                    <p class="mt-6 text-gray-500 leading-relaxed">
                        O Laravel Jetstream fornece um ponto de partida bonito e robusto para a sua próxima aplicação
                        Laravel. O Laravel foi projetado
                        para ajudá-lo a construir sua aplicação usando um ambiente de desenvolvimento simples, poderoso
                        e agradável. Acreditamos
                        que você deve amar expressar sua criatividade através da programação, então dedicamos tempo para
                        criar cuidadosamente o ecossistema do Laravel
                        para ser um sopro de ar fresco. Esperamos que você goste.
                    </p>
                </div>

                <div class="bg-gray-200 bg-opacity-25 grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8 p-6 lg:p-8">
                    <div>
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                class="w-6 h-6 stroke-gray-400">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                            </svg>
                            <h2 class="ms-3 text-xl font-semibold text-gray-900">
                                <a href="https://laravel.com/docs">Documentação</a>
                            </h2>
                        </div>

                        <p class="mt-4 text-gray-500 text-sm leading-relaxed">
                            O Laravel possui uma documentação que abrange todos os aspectos do framework. Se você é novo
                            no framework ou tem experiência anterior, recomenda-se ler toda a documentação do início ao
                            fim.
                        </p>

                        <p class="mt-4 text-sm">
                            <a href="https://laravel.com/docs"
                                class="inline-flex items-center font-semibold text-primary-700 hover:text-primary-800 transition-colors">
                                Explorar a documentação

                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                    class="ms-1 w-5 h-5 fill-primary-700 hover:fill-primary-800 transition-colors">
                                    <path fill-rule="evenodd"
                                        d="M5 10a.75.75 0 01.75-.75h6.638L10.23 7.29a.75.75 0 111.04-1.08l3.5 3.25a.75.75 0 010 1.08l-3.5 3.25a.75.75 0 11-1.04-1.08l2.158-1.96H5.75A.75.75 0 015 10z"
                                        clip-rule="evenodd" />
                                </svg>
                            </a>
                        </p>
                    </div>


                    <div>
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                class="w-6 h-6 stroke-gray-400">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                            </svg>
                            <h2 class="ms-3 text-xl font-semibold text-gray-900">
                                <a href="https://tailwindcss.com/">Tailwind CSS</a>
                            </h2>
                        </div>

                        <p class="mt-4 text-gray-500 text-sm leading-relaxed">
                            O Laravel Jetstream é construído com o Tailwind, um incrível framework CSS de utilidades.
                            Você ficará surpreso com a facilidade com que pode criar e manter designs modernos e
                            atualizados com este framework ao seu alcance.
                        </p>
                    </div>

                </div>
            </div>
        </div>
    </AppLayout>
</template>
