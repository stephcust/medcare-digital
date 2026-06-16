<script setup>
import Drawer from 'primevue/drawer';
import PanelMenu from 'primevue/panelmenu';
import { useSidebar } from './Composables';
import { Link } from '@inertiajs/vue3';

const sidebar = useSidebar();
</script>

<template>
    <Drawer header="MedCare Digital" class="border-none shadow" pt:header:class="max-h-[75px] text-white bg-primary-900"
        pt:pcCloseButton:root:class="!text-white hover:bg-primary-600" v-model:visible="sidebar.state.visible"
        :modal="true">
        <div class="text-lg font-semibold mt-4 mb-1">
            <!-- Todos os módulos -->
        </div>
        <PanelMenu :model="sidebar.state.items" class="!w-[17.4rem]">
            <template #item="{ item }">
                <Link v-if="item.url || item.href" v-ripple
                    class="flex items-center cursor-pointer text-surface-700 dark:text-surface-0 px-4 py-2"
                    :href="item.url || item.href" @click="sidebar.toggle()">
                <i :class="item.icon"></i>
                <span class="ml-2">{{ item.label }}</span>
                </Link>
                <a v-else v-ripple
                    class="flex items-center cursor-pointer text-surface-700 dark:text-surface-0 px-4 py-2">
                    <i :class="item.icon"></i>
                    <span class="ml-2">{{ item.label }}</span>
                    <span v-if="item.items" class="pi pi-angle-down text-primary ml-auto" />
                </a>
            </template>
        </PanelMenu>
    </Drawer>
</template>
