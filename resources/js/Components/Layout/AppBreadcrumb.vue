<script setup>
import { useApp } from '@/Assets/Composables';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ title: String });

const app = useApp();
const trail = computed(() => app.breadcrumbs?.slice(0, -1));
const active = computed(() => app.breadcrumbs?.at(-1));
const pageTitle = computed(() => active.value?.title ?? props.title);
const actions = computed(() => active.value?.actions ?? []);

function actionClass(action) {
    const base = 'inline-flex items-center gap-2 rounded-lg px-4 py-2.5 text-sm font-semibold shadow-sm transition focus:outline-none focus:ring-2';

    if (action.variant === 'primary') {
        return `${base} bg-primary-600 text-white shadow-primary-900/20 hover:bg-primary-700 focus:ring-primary-300`;
    }

    if (action.variant === 'danger') {
        return `${base} border border-red-200 bg-white text-red-600 hover:border-red-300 hover:bg-red-50 focus:ring-red-200`;
    }

    return `${base} border border-surface-200 bg-white text-surface-700 hover:border-primary-200 hover:bg-primary-50 hover:text-primary-700 focus:ring-primary-200`;
}
</script>

<template>
    <div v-if="app.breadcrumbs?.length > 0"
        class="mx-auto mb-3 max-w-7xl rounded-xl border border-surface-200 bg-white/95 px-4 py-4 shadow-sm sm:px-5">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="min-w-0">
                <ul
                    class="mb-2 flex list-none items-center gap-2 p-0 text-xs font-semibold uppercase tracking-wide text-surface-500">
                    <li class="flex items-center">
                        <i class="pi pi-home text-[0.7rem] text-primary-500"></i>
                    </li>
                    <li v-if="trail.length > 0" class="text-surface-300">
                        <i class="pi pi-angle-right text-[0.65rem]"></i>
                    </li>
                    <template v-for="(t, i) in trail" :key="`${t.title}-${i}`">
                        <li>
                            <Link v-if="t.url" :href="t.url"
                                class="rounded-md px-1.5 py-1 text-surface-500 transition hover:bg-surface-100 hover:text-primary-700">
                            {{ t.title }}
                            </Link>
                            <span v-else class="px-1.5 py-1">{{ t.title }}</span>
                        </li>
                        <li class="text-surface-300">
                            <i class="pi pi-angle-right text-[0.65rem]"></i>
                        </li>
                    </template>
                    <li class="truncate rounded-md bg-primary-50 px-2 py-1 text-primary-700">
                        {{ pageTitle }}
                    </li>
                </ul>

                <h1 class="truncate text-2xl font-semibold text-surface-950 sm:text-3xl">
                    {{ pageTitle }}
                </h1>
            </div>

            <div class="flex shrink-0 flex-wrap items-center justify-start gap-2 lg:justify-end">
                <slot />
                <Link v-for="(action, i) in actions" :key="`${action.label}-${i}`" :href="action.url"
                    :class="actionClass(action)">
                <i v-if="action.icon" :class="[action.icon, 'text-xs']"></i>
                {{ action.label }}
                </Link>
            </div>
        </div>
    </div>
</template>
