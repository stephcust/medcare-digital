<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import Button from 'primevue/button';
import Divider from 'primevue/divider';

</script>

<template>
    <PublicLayout>
        <div class="max-w-7xl mx-auto rounded p-2 flex flex-col gap-2">
            <!-- Apresentação -->
            <div class="flex lg:flex-row flex-col gap-4 bg-surface-0 dark:bg-surface-900">
                <div class="flex-1 flex items-center justify-center">
                    <div class="p-6 pt-12 lg:p-12">
                        <h1
                            class="text-3xl lg:text-5xl font-bold text-surface-900 dark:text-surface-0 mb-4 lg:leading-normal text-center lg:text-left">
                            Crie aplicações que seus <br /><span
                                class="text-primary-600 dark:text-primary-400">visitantes
                                querem ver</span>
                        </h1>
                        <p class="text-surface-700 dark:text-surface-200 leading-normal mb-8 text-center lg:text-left">
                            Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut
                            labore et dolore magna aliqua.
                        </p>
                        <div class="flex items-center justify-center lg:justify-start gap-6">
                            <Button label="Saiba Mais" type="button" />
                            <Button label="Link Externo" type="button" outlined />
                        </div>
                    </div>
                </div>
                <div class="flex-1 overflow-hidden">
                    <img src="https://fqjltiegiezfetthbags.supabase.co/storage/v1/render/image/public/block.images/blocks/hero/hero-1.png"
                        alt="hero-1"
                        class="h-full w-full object-cover lg:[clip-path:polygon(12%_0,100%_0%,100%_100%,0_100%)]" />
                </div>
            </div>

            <Divider />

            <!-- Detalhamento -->
            <div class="bg-surface-0 dark:bg-surface-950 px-6 py-6 text-center">
                <div class="mb-4 font-bold text-3xl">
                    <span class="text-surface-900 dark:text-surface-0">Um Produto, </span>
                    <span class="text-primary-600 dark:text-primary-400">Muitas Soluções</span>
                </div>
                <div class="text-surface-700 dark:text-surface-0/70 mb-[3rem]">Ac turpis egestas maecenas pharetra
                    convallis posuere morbi leo urna.</div>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-x-3.5 gap-y-6">
                    <div class="w-full p-4">
                        <span
                            class="w-16 h-16 mb-6 rounded-lg flex items-center justify-center mx-auto bg-surface-0 dark:bg-surface-800 shadow-[0px_2px_6px_0px_rgba(0,0,0,0.12),0px_0px_2px_0px_rgba(0,0,0,0.06),0px_4px_10px_0px_rgba(0,0,0,0.03)]">
                            <i class="pi pi-desktop text-2xl lg:text-3xl text-primary-500 dark:text-primary-400" />
                        </span>
                        <div class="text-surface-900 dark:text-surface-0 text-xl mb-2 font-medium">Built for Developers
                        </div>
                        <p class="text-surface-700 dark:text-surface-0/70 leading-normal max-w-sm mx-auto text-center">
                            Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla
                            pariatur.</p>
                    </div>
                    <div class="w-full p-4">
                        <span
                            class="w-16 h-16 mb-6 rounded-lg flex items-center justify-center mx-auto bg-surface-0 dark:bg-surface-800 shadow-[0px_2px_6px_0px_rgba(0,0,0,0.12),0px_0px_2px_0px_rgba(0,0,0,0.06),0px_4px_10px_0px_rgba(0,0,0,0.03)]">
                            <i class="pi pi-lock text-2xl lg:text-3xl text-primary-500 dark:text-primary-400" />
                        </span>
                        <div class="text-surface-900 dark:text-surface-0 text-xl mb-2 font-medium">End-to-End Encryption
                        </div>
                        <p class="text-surface-700 dark:text-surface-0/70 leading-normal max-w-sm mx-auto text-center">
                            Risus nec feugiat in fermentum posuere urna nec. Posuere sollicitudin aliquam ultrices
                            sagittis.</p>
                    </div>
                    <div class="w-full p-4">
                        <span
                            class="w-16 h-16 mb-6 rounded-lg flex items-center justify-center mx-auto bg-surface-0 dark:bg-surface-800 shadow-[0px_2px_6px_0px_rgba(0,0,0,0.12),0px_0px_2px_0px_rgba(0,0,0,0.06),0px_4px_10px_0px_rgba(0,0,0,0.03)]">
                            <i class="pi pi-check-circle text-2xl lg:text-3xl text-primary-500 dark:text-primary-400" />
                        </span>
                        <div class="text-surface-900 dark:text-surface-0 text-xl mb-2 font-medium">Easy to Use</div>
                        <p class="text-surface-700 dark:text-surface-0/70 leading-normal max-w-sm mx-auto text-center">
                            Ornare suspendisse sed nisi lacus sed viverra tellus. Neque volutpat ac tincidunt vitae
                            semper.</p>
                    </div>
                    <div class="w-full p-4">
                        <span
                            class="w-16 h-16 mb-6 rounded-lg flex items-center justify-center mx-auto bg-surface-0 dark:bg-surface-800 shadow-[0px_2px_6px_0px_rgba(0,0,0,0.12),0px_0px_2px_0px_rgba(0,0,0,0.06),0px_4px_10px_0px_rgba(0,0,0,0.03)]">
                            <i class="pi pi-globe text-2xl lg:text-3xl text-primary-500 dark:text-primary-400" />
                        </span>
                        <div class="text-surface-900 dark:text-surface-0 text-xl mb-2 font-medium">Fast & Global Support
                        </div>
                        <p class="text-surface-700 dark:text-surface-0/70 leading-normal max-w-sm mx-auto text-center">
                            Fermentum et sollicitudin ac orci phasellus egestas tellus rutrum tellus.</p>
                    </div>
                    <div class="w-full p-4">
                        <span
                            class="w-16 h-16 mb-6 rounded-lg flex items-center justify-center mx-auto bg-surface-0 dark:bg-surface-800 shadow-[0px_2px_6px_0px_rgba(0,0,0,0.12),0px_0px_2px_0px_rgba(0,0,0,0.06),0px_4px_10px_0px_rgba(0,0,0,0.03)]">
                            <i class="pi pi-github text-2xl lg:text-3xl text-primary-500 dark:text-primary-400" />
                        </span>
                        <div class="text-surface-900 dark:text-surface-0 text-xl mb-2 font-medium">Open Source</div>
                        <p class="text-surface-700 dark:text-surface-0/70 leading-normal max-w-sm mx-auto text-center">
                            Nec tincidunt praesent semper feugiat. Sed adipiscing diam donec adipiscing tristique risus
                            nec feugiat.</p>
                    </div>
                    <div class="w-full p-4">
                        <span
                            class="w-16 h-16 mb-6 rounded-lg flex items-center justify-center mx-auto bg-surface-0 dark:bg-surface-800 shadow-[0px_2px_6px_0px_rgba(0,0,0,0.12),0px_0px_2px_0px_rgba(0,0,0,0.06),0px_4px_10px_0px_rgba(0,0,0,0.03)]">
                            <i class="pi pi-shield text-2xl lg:text-3xl text-primary-500 dark:text-primary-400" />
                        </span>
                        <div class="text-surface-900 dark:text-surface-0 text-xl mb-2 font-medium">Trusted Security
                        </div>
                        <p class="text-surface-700 dark:text-surface-0/70 leading-normal max-w-sm mx-auto text-center">
                            Mattis rhoncus urna neque viverra justo nec ultrices. Id cursus metus aliquam eleifend.</p>
                    </div>
                </div>
            </div>

            <Divider class="max-w-7xl mx-auto" />
        </div>
    </PublicLayout>
</template>
