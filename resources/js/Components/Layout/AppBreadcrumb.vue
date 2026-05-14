<script setup>
import { useApp } from '@/Assets/Composables';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({ title: String });

const app = useApp();
const trail = computed(() => app.breadcrumbs?.slice(0, -1));
const active = computed(() => app.breadcrumbs?.at(-1));
</script>

<template>
    <div v-if="app.breadcrumbs?.length > 0"
        class="max-w-7xl mx-auto p-1 pb-2 flex flex-col lg:justify-between lg:flex-row">
        <div class="flex flex-col">
            <div>
                <div class="font-medium text-3xl text-surface-900 dark:text-surface-0">{{ active.title }}</div>
            </div>
            <ul
                class="list-none p-0 m-0 flex items-center font-medium text-surface-500 dark:text-surface-300 leading-normal">
                <template v-for="(t, i) in trail" :key="t.url">
                    <li>
                        <Link v-if="t.url" :href="t.url"
                            class="cursor-pointer hover:text-primary-500 transition-all duration-300">
                        {{ t.title }}
                        </Link>
                        <span v-else>{{ t.title }}</span>
                    </li>
                    <li v-if="(i + 1) < trail.length" class="px-2">
                        <i class="pi pi-angle-right"></i>
                    </li>
                </template>
            </ul>
        </div>

        <div class="flex items-center">
            <slot />
        </div>
    </div>
</template>
