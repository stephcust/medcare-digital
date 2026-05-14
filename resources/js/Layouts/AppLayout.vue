<script setup>
import AppBreadcrumb from '@/Components/Layout/AppBreadcrumb.vue';
import AppFooter from '@/Components/Layout/AppFooter.vue';
import AppHeader from '@/Components/Layout/AppHeader.vue';
import AppSidebar from '@/Components/Layout/AppSidebar.vue';
import { useSidebar } from '@/Components/Layout/Composables';
import { Head, Link } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Toast from 'primevue/toast';

defineProps({
    title: String, hideBreadcrumb: {
        type: Boolean,
        default: false,
    },
    hasBackButton: {
        type: Boolean,
        default: false,
    },
    hasCreateButton: {
        type: Boolean,
        default: false,
    },
    routeBackButton: {
        type: String,
        default: '#',
    },
    routeCreateButton: {
        type: String,
        default: '#',
    },
});

const buttonAdicionar = 'p-button bg-primary hover:bg-emerald-600';
const buttonVoltar = 'p-button bg-sky-600 border-sky-600 hover:bg-sky-700 hover:border-sky-700';

const sidebar = useSidebar();
</script>

<template>
    <!-- Componentes Ocultos -->

    <Head :title />
    <Toast />
    <!-- App Shell -->
    <div class="app-shell">
        <!-- Sidebar -->
        <aside>
            <AppSidebar />
        </aside>
        <!-- Header -->
        <header>
            <AppHeader />
        </header>
        <main class="p-2">
            <AppBreadcrumb v-if="!hideBreadcrumb" :title>
                <slot name="breadcrumbRight" />
                <div class="flex gap-2">
                    <Link v-if="hasCreateButton" :class="buttonAdicionar" :href="routeCreateButton">
                    <i class="pi pi-plus-circle"></i>
                    Adicionar
                    </Link>
                    <Link v-if="hasBackButton" :class="buttonVoltar" :href="routeBackButton">
                    <i class="pi pi-arrow-left"></i>
                    Voltar
                    </Link>
                </div>
            </AppBreadcrumb>
            <!-- Conteúdo da Página -->
            <div class="max-w-7xl mx-auto p-1 pb-2 flex flex-col lg:justify-between lg:flex-row">
                <slot />
            </div>
        </main>
        <footer>
            <AppFooter />
        </footer>
    </div>

</template>

<style scoped>
.app-shell {
    display: grid;
    grid-template-rows: auto 1fr auto;
    grid-template-columns: 0rem 1fr;
    grid-template-areas:
        "sidebar header"
        "sidebar main"
        "sidebar footer";
    height: 100vh;
}

.app-shell main {
    grid-area: main;
}

.app-shell header {
    grid-area: header;
}

.app-shell footer {
    grid-area: footer;
}

.app-shell aside {
    grid-area: sidebar;
    background-color: #f8f9fa;
    border-right: 1px solid #e9ecef;
}
</style>
