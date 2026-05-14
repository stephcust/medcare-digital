<script setup>
/** @import { MenuItem } from '@/Assets/GlobalTypes'; */
import LogoPMMHorizontal from '@/Components/Logos/LogoPMMHorizontal.vue';
import { Link } from '@inertiajs/vue3';
import { PrimeIcons as PI } from '@primevue/core/api';
import Button from 'primevue/button';
import Menubar from 'primevue/menubar';
import { ref } from 'vue';
import { isMenuActive } from '@/Components/Layout/Functions';

/** @type {MenuItem[]} */
const inicialItems = [{
    // label: 'Sobre',
    // icon: PI.QUESTION_CIRCLE,
    // items: [
    //     {
    //         label: 'Política de Privacidade',
    //         icon: PI.INFO_CIRCLE,
    //         route: 'policy.show',
    //     },
    //     {
    //         label: 'Termos de Serviço',
    //         icon: 'pi pi-file-check',
    //         route: 'terms.show',
    //     }
    // ]
}]
const topNavItems = ref(inicialItems);
</script>

<template>
    <nav class="text-white shadow-md bg-primary-800">
        <Menubar :model="topNavItems"
            class="min-h-[75px] max-h-[75px] text-white border-none rounded-none max-w-7xl mx-auto bg-primary-800">
            <template #start>
                <LogoPMMHorizontal />
            </template>

            <template #item="{ item, props, hasSubmenu, root }">
                <!-- Com Navegação -->
                <Link v-if="item.route" :href="route(item.route)" v-ripple
                    :class="{ 'text-primary-500 font-semibold': isMenuActive(item) }"
                    class="flex items-center rounded-[4px]" v-bind="props.action">
                <span v-if="item.icon" class="mr-1" :class="item.icon" />
                <span>{{ item.label }}</span>
                <i v-if="hasSubmenu"
                    :class="['pi pi-angle-down', { 'pi-angle-down ml-2': root, 'pi-angle-right ml-auto': !root }]"></i>
                </Link>
                <!-- Sem Navegação -->
                <a v-else v-ripple :class="{ 'text-primary-500': isMenuActive(item) }"
                    class="flex items-center rounded-[4px]" v-bind="props.action">
                    <span v-if="item.icon" class="mr-1" :class="item.icon" />
                    <span>{{ item.label }}</span>
                    <i v-if="hasSubmenu"
                        :class="['pi pi-angle-down', { 'pi-angle-down ml-2': root, 'pi-angle-right ml-auto': !root }]"></i>
                </a>
            </template>

            <template #end>
                <div class="hidden space-x-6 sm:flex text-lg">
                    <Link v-if="route().has('login')" :href="route('login')"
                        class="transition-all align-text-top hover:text-primary">
                    Entrar
                    </Link>

                    <Link v-if="route().has('register')" :href="route('register')"
                        class="transition-all hover:text-primary">
                    Cadastrar
                    </Link>
                </div>
                <div class="sm:hidden space-x-1">
                    <Link v-if="route().has('login')" :href="route('login')">
                    <Button class="text-white hover:text-primary-700" v-tooltip.left="'Entrar'" icon="pi pi-sign-in" text />
                    </Link>
                    <Link v-if="route().has('register')" :href="route('register')">
                    <Button class="text-white hover:text-primary-700" v-tooltip.left="'Cadastrar'" icon="pi pi-pen-to-square" text />
                    </Link>
                </div>
            </template>
        </Menubar>
    </nav>
</template>
