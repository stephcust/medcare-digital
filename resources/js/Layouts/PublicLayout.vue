<script setup>
import AppFooter from '@/Components/Layout/AppFooter.vue';
import PublicHeader from '@/Components/Layout/PublicHeader.vue';
import { Head } from '@inertiajs/vue3';
import Toast from 'primevue/toast';

defineProps({ title: String });
</script>

<template>

    <Head :title />
    <Toast />
    <div class="min-h-screen bg-surface-0">
        <!-- Header -->
        <PublicHeader />

        <!-- Conteúdo da Página -->
        <main>
            <slot />
        </main>

        <!-- Footer -->
        <AppFooter />
    </div>
</template>
