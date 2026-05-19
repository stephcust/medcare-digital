<script setup>
import AppBreadcrumb from '@/Components/Layout/AppBreadcrumb.vue';
import AppFooter from '@/Components/Layout/AppFooter.vue';
import AppHeader from '@/Components/Layout/AppHeader.vue';
import AppSidebar from '@/Components/Layout/AppSidebar.vue';
import { useSidebar } from '@/Components/Layout/Composables';
import { Head, Link } from '@inertiajs/vue3';
import Toast from 'primevue/toast';
import { computed } from 'vue';

const props = defineProps({
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

const currentRoute = computed(() => route().current());
const shouldShowCreateButton = computed(() => props.hasCreateButton || currentRoute.value === 'exames.index');
const shouldShowBackButton = computed(() => props.hasBackButton || ['exames.create', 'exames.show'].includes(currentRoute.value));
const createButtonHref = computed(() => props.routeCreateButton !== '#' ? props.routeCreateButton : route('exames.create'));
const backButtonHref = computed(() => props.routeBackButton !== '#' ? props.routeBackButton : route('exames.index'));

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
                <div class="flex flex-wrap gap-2">
                    <Link v-if="shouldShowCreateButton"
                        class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-primary-900/20 transition hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-300"
                        :href="createButtonHref">
                    <i class="pi pi-plus-circle text-xs"></i>
                    Anexar exame
                    </Link>
                    <Link v-if="shouldShowBackButton"
                        class="inline-flex items-center gap-2 rounded-lg border border-surface-200 bg-white px-4 py-2.5 text-sm font-semibold text-surface-700 shadow-sm transition hover:border-primary-200 hover:bg-primary-50 hover:text-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-200"
                        :href="backButtonHref">
                    <i class="pi pi-arrow-left text-xs"></i>
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
