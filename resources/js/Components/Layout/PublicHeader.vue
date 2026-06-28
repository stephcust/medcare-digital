<script setup>
import { Link } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Menubar from 'primevue/menubar';

const topNavItems = [];
</script>

<template>
    <nav class="sticky top-0 z-50 border-b border-surface-200 bg-white/95 shadow-sm backdrop-blur">
        <Menubar
            :model="topNavItems"
            class="mx-auto min-h-[76px] max-w-7xl rounded-none border-none bg-transparent px-4 sm:px-6 lg:px-8"
        >
            <template #start>
                <Link :href="route('landingPage')" class="flex items-center gap-3" aria-label="PÃ¡gina inicial do MedCare Digital">
                    <img
    src="/images/logo-medcare-simbolo.png"
    alt="Símbolo oficial da MedCare"
    class="h-12 w-12 object-contain"
/>
                    <span class="leading-none">
                        <span class="block text-xl font-extrabold tracking-tight text-surface-950">MedCare</span>
                        <span class="mt-1 block text-[10px] font-bold uppercase tracking-[0.22em] text-primary-600">Digital</span>
                    </span>
                </Link>
            </template>

            <template #button>
                <span></span>
            </template>

            <template #buttonIcon>
                <span></span>
            </template>

            <template #end>
                <div class="hidden items-center gap-3 sm:flex">
                    <Link v-if="route().has('login')" :href="route('login')">
                        <Button label="Entrar" text class="!font-bold !text-surface-700 hover:!text-primary-700" />
                    </Link>

                    <Link v-if="route().has('register')" :href="route('register')">
                        <Button label="Criar conta" icon="pi pi-arrow-right" iconPos="right" class="!rounded-xl !px-5 !font-bold" />
                    </Link>
                </div>

                <div class="flex items-center gap-1 sm:hidden">
                    <Link v-if="route().has('login')" :href="route('login')">
                        <Button icon="pi pi-sign-in" text rounded aria-label="Entrar" />
                    </Link>
                    <Link v-if="route().has('register')" :href="route('register')">
                        <Button icon="pi pi-user-plus" rounded aria-label="Criar conta" />
                    </Link>
                </div>
            </template>
        </Menubar>
    </nav>
</template>
