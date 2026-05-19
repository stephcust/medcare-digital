<script setup>
/** @import { MenuItem } from '@/Assets/GlobalTypes'; */
import { useSidebar } from '@/Components/Layout/Composables';
import { recursiveMenuItem } from '@/Components/Layout/Functions';
import LogoPMMHorizontal from '@/Components/Logos/LogoHorizontal.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { PrimeIcons as PI } from '@primevue/core/api';
import Button from 'primevue/button';
import Menu from 'primevue/menu';
import Menubar from 'primevue/menubar';
import { computed, ref, watchEffect } from 'vue';

const sidebar = useSidebar();

/** @type {MenuItem[]} */
const inicialNavItems = [
    {
        label: 'Início',
        icon: PI.HOME,
        route: 'dashboard',
    },
    {
        label: 'Exames',
        icon: PI.FILE_PDF,
        items: [
            {
                label: 'Meus Exames',
                icon: PI.LIST,
                route: 'exames.index',
            },
            {
                label: 'Anexar Exame',
                icon: PI.PLUS_CIRCLE,
                route: 'exames.create',
            },
            // {
            //     label: 'CRUD Multi Page',
            //     icon: PI.TABLE,
            //     route: 'tarefa.index',
            // },
        ],
    },
];

/** @type {MenuItem[]} */
const inicialUserMenuItems = [
    {
        label: 'Gerenciar Conta',
    },
    {
        label: 'Perfil',
        icon: PI.USER_EDIT,
        route: 'profile.show',
    },
];

const page = usePage();
const userMenu = ref(null);
const isAuthenticated = computed(() => Boolean(page.props.auth?.user));

const topNavUserMenuItems = computed(() => {
    const items = structuredClone(inicialUserMenuItems);

    if (page.props.jetstream?.hasApiFeatures) {
        items.push({
            label: 'Tokens de API',
            icon: PI.KEY,
            route: 'api-tokens.index',
        });
    }

    items.push({
        label: 'Sair',
        icon: PI.SIGN_OUT,
        command: logout,
    });

    return items;
});

const _topNavUserMenuItems = computed(() => {
    return topNavUserMenuItems.value.map(recursiveMenuItem);
});

watchEffect(() => {
    sidebar.setItems(isAuthenticated.value ? inicialNavItems.map(recursiveMenuItem) : []);
});

function toggleUserMenu(e) {
    if (!isAuthenticated.value) {
        return;
    }

    userMenu.value.toggle(e);
}

function logout() {
    if (!isAuthenticated.value) {
        return;
    }

    router.post(route('logout'));
}
</script>

<template>
    <div class="bg-primary-800">
        <Menubar :model="[]"
            class="min-h-[75px] max-h-[75px] bg-primary-800 text-white border-none rounded-none max-w-7xl mx-auto"
            pt:end:class="flex gap-2">
            <template #start>
                <div class="flex items-center gap-4">
                    <Link href="/home">
                        <LogoPMMHorizontal />
                    </Link>

                    <Button v-if="sidebar.state.items.length > 0" text
                        class="p-2 rounded-full m-0 hover:bg-primary-600 cursor-pointer"
                        :class="{ 'bg-primary-600': sidebar.state.visible }" @click="sidebar.toggle">
                        <i class="pi pi-bars text-white text-xl"></i>
                    </Button>
                </div>
            </template>

            <template #button>
                <span></span>
            </template>

            <template #buttonIcon>
                <span></span>
            </template>

            <template #end>
                <button v-if="isAuthenticated" @click="toggleUserMenu"
                    class="rounded-full bg-primary-900 p-1 grid place-content-center text-white hover:bg-primary-500 focus:outline-none focus:bg-primary-500 active:bg-primary-500 transition ease-in-out duration-300">
                    <div
                        class="sm:hidden m-0 p-0 flex text-sm rounded-full focus:outline-none focus:border-gray-300 transition items-center justify-center">
                        <img v-if="$page.props.jetstream.managesProfilePhotos"
                            class="object-cover h-8 w-8 border-2 border-transparent rounded-full"
                            :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">
                        <i v-else class="pi pi-user"></i>
                    </div>
                    <div class="hidden sm:inline-flex items-center px-1 text-sm font-medium">
                        <div v-if="$page.props.jetstream.managesProfilePhotos"
                            class="flex text-sm border-2 border-transparent mr-1 rounded-full focus:outline-none focus:border-gray-300 transition">
                            <img class="h-8 w-8 rounded-full object-cover"
                                :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">
                        </div>
                        <span>{{ $page.props.auth.user.short_name }}</span>
                        <i class="pi pi-angle-down ml-2"></i>
                    </div>
                </button>

                <Menu v-if="isAuthenticated" ref="userMenu" :model="_topNavUserMenuItems" popup>
                    <template #item="{ item, props }">
                        <span v-if="!item.hasPointer"
                            class="flex items-center cursor-default text-sm ml-2 font-semibold py-1"
                            @click.prevent.stop="null">
                            <span :class="item.icon" />
                            <span>{{ item.label }}</span>
                        </span>
                        <Link v-else-if="item.url" class="flex items-center" :href="item.url" v-ripple
                            v-bind="props.action">
                        <span :class="item.icon" />
                        <span>{{ item.label }}</span>
                        </Link>
                        <a v-else-if="item.command" class="flex items-center" v-ripple v-bind="props.action">
                            <span :class="item.icon" />
                            <span>{{ item.label }}</span>
                        </a>
                    </template>
                </Menu>
            </template>
        </Menubar>
    </div>
</template>
