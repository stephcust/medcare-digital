<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import Button from 'primevue/button';
import Divider from 'primevue/divider';
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

</script>

<template>
    <PublicLayout>
        <div class="max-w-7xl mx-auto rounded p-2 flex flex-col gap-2">
            <div class="flex lg:flex-row flex-col gap-4 bg-surface-0 dark:bg-surface-900 overflow-hidden rounded-lg">
                <div class="flex-1 flex items-center justify-center">
                    <div class="p-6 pt-12 lg:p-12">
                        <h1
                            class="text-3xl lg:text-5xl font-bold text-surface-900 dark:text-surface-0 mb-4 lg:leading-normal text-center lg:text-left">
                            Toda sua jornada de saúde <br />
                            <span class="text-primary-600 dark:text-primary-400">em um só lugar</span>
                        </h1>
                        <p class="text-surface-700 dark:text-surface-200 leading-normal mb-8 text-center lg:text-left">
                            A MedCare é sua carteira digital inteligente. Centralize exames, receitas, vacinas e
                            consulte sua rede credenciada do setor privado com a facilidade e a exclusividade que você merece.
                        </p>
                        <div class="flex items-center justify-center lg:justify-start gap-6">
                            <Link :href="route('register')">
                                <Button label="Começar Agora" type="button" icon="pi pi-user-plus" />
                            </Link>
                            <Button label="Conhecer Soluções" type="button" outlined icon="pi pi-info-circle" />
                        </div>
                    </div>
                </div>
                <div class="flex-1 overflow-hidden">
                    <img src='/storage/image-medcare1.jpg'
                        alt="MedCare Saúde Digital"
                        class="h-full w-full object-cover lg:[clip-path:polygon(12%_0,100%_0%,100%_100%,0_100%)]" />
                </div>
            </div>

            <Divider />

            <div class="bg-surface-0 dark:bg-surface-950 px-6 py-12 text-center rounded-lg">
                <div class="mb-4 font-bold text-3xl">
                    <span class="text-surface-900 dark:text-surface-0">Sua saúde </span>
                    <span class="text-primary-600 dark:text-primary-400">Totalmente Integrada</span>
                </div>
                <div class="text-surface-700 dark:text-surface-0/70 mb-[3rem] max-w-2xl mx-auto">
                    Esqueça as pastas de papel e PDFs espalhados. Conectamos você às clínicas, laboratórios e ao seu plano de saúde de forma inteligente.
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-x-3.5 gap-y-6">
                    <div class="w-full p-4">
                        <span
                            class="w-16 h-16 mb-6 rounded-lg flex items-center justify-center mx-auto bg-surface-0 dark:bg-surface-800 shadow-md">
                            <i class="pi pi-history text-2xl lg:text-3xl text-primary-500 dark:text-primary-400" />
                        </span>
                        <div class="text-surface-900 dark:text-surface-0 text-xl mb-2 font-medium">Histórico Completo</div>
                        <p class="text-surface-700 dark:text-surface-0/70 leading-normal max-w-sm mx-auto text-center">
                            Acesse prontuários, exames e históricos de internação realizados em qualquer clínica privada parceira.
                        </p>
                    </div>

                    <div class="w-full p-4">
                        <span
                            class="w-16 h-16 mb-6 rounded-lg flex items-center justify-center mx-auto bg-surface-0 dark:bg-surface-800 shadow-md">
                            <i class="pi pi-map-marker text-2xl lg:text-3xl text-primary-500 dark:text-primary-400" />
                        </span>
                        <div class="text-surface-900 dark:text-surface-0 text-xl mb-2 font-medium">Guia Inteligente</div>
                        <p class="text-surface-700 dark:text-surface-0/70 leading-normal max-w-sm mx-auto text-center">
                            Cadastre seu plano e descubra instantaneamente quais clínicas e médicos atendem você na sua região.
                        </p>
                    </div>

                    <div class="w-full p-4">
                        <span
                            class="w-16 h-16 mb-6 rounded-lg flex items-center justify-center mx-auto bg-surface-0 dark:bg-surface-800 shadow-md">
                            <i class="pi pi-wallet text-2xl lg:text-3xl text-primary-500 dark:text-primary-400" />
                        </span>
                        <div class="text-surface-900 dark:text-surface-0 text-xl mb-2 font-medium">Carteira de Vacinação</div>
                        <p class="text-surface-700 dark:text-surface-0/70 leading-normal max-w-sm mx-auto text-center">
                            Controle suas doses e receitas médicas digitais. Receba alertas de retorno e validade de medicamentos.
                        </p>
                    </div>

                    <div class="w-full p-4">
                        <span
                            class="w-16 h-16 mb-6 rounded-lg flex items-center justify-center mx-auto bg-surface-0 dark:bg-surface-800 shadow-md">
                            <i class="pi pi-sync text-2xl lg:text-3xl text-primary-500 dark:text-primary-400" />
                        </span>
                        <div class="text-surface-900 dark:text-surface-0 text-xl mb-2 font-medium">Sincronização Total</div>
                        <p class="text-surface-700 dark:text-surface-0/70 leading-normal max-w-sm mx-auto text-center">
                            Integração nativa entre laboratórios e consultórios. O resultado do seu exame cai direto na sua linha do tempo.
                        </p>
                    </div>

                    <div class="w-full p-4">
                        <span
                            class="w-16 h-16 mb-6 rounded-lg flex items-center justify-center mx-auto bg-surface-0 dark:bg-surface-800 shadow-md">
                            <i class="pi pi-exclamation-triangle text-2xl lg:text-3xl text-primary-500 dark:text-primary-400" />
                        </span>
                        <div class="text-surface-900 dark:text-surface-0 text-xl mb-2 font-medium">Dados de Emergência</div>
                        <p class="text-surface-700 dark:text-surface-0/70 leading-normal max-w-sm mx-auto text-center">
                            Mantenha alergias e restrições visíveis para equipes de Pronto-Socorro, garantindo um atendimento seguro.
                        </p>
                    </div>

                    <div class="w-full p-4">
                        <span
                            class="w-16 h-16 mb-6 rounded-lg flex items-center justify-center mx-auto bg-surface-0 dark:bg-surface-800 shadow-md">
                            <i class="pi pi-shield text-2xl lg:text-3xl text-primary-500 dark:text-primary-400" />
                        </span>
                        <div class="text-surface-900 dark:text-surface-0 text-xl mb-2 font-medium">Privacidade Rigorosa</div>
                        <p class="text-surface-700 dark:text-surface-0/70 leading-normal max-w-sm mx-auto text-center">
                            Seus dados de saúde são protegidos com criptografia de ponta a ponta e total conformidade com a LGPD.
                        </p>
                    </div>
                </div>
            </div>

            <Divider class="max-w-7xl mx-auto" />
        </div>
    </PublicLayout>
</template>
