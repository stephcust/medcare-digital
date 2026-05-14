<script setup>
/** @import { MenuItem } from '@/Assets/GlobalTypes'; */
import { useApp } from '@/Assets/Composables';
import Notifications from '@/Components/Layout/Notifications.vue';
import { useSidebar } from '@/Components/Layout/Composables';
import { isMenuActive, recursiveMenuItem } from '@/Components/Layout/Functions';
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
        label: 'Exemplos',
        icon: PI.SEARCH,
        items: [
            {
                label: 'Dashboard',
                icon: PI.CHART_LINE,
                route: 'exemplos.dashboard',
            },
            {
                label: 'Primevue',
                icon: PI.PRIME,
                route: 'exemplos.primevue',
            },
            {
                label: 'CRUD Multi Page',
                icon: PI.TABLE,
                route: 'tarefa.index',
            },
        ]
    },
];

/** @type {MenuItem[]} */
const inicialUserMenuItems = [
    {
        label: 'Gerenciar Conta'
    },
    {
        label: 'Perfil',
        icon: PI.USER_EDIT,
        route: 'profile.show',
    },
];
//* States
const page = usePage();
const topNavItems = ref(inicialNavItems);
const topNavUserMenuItems = computed(() => {
    // Itens do menu do usuário logado
    const items = structuredClone(inicialUserMenuItems);
    // Caso Tokens de API esteja habilitado
    if (page.props.jetstream?.hasApiFeatures) {
        items.push({
            label: 'Tokens de API',
            icon: PI.KEY,
            route: 'api-tokens.index',
        })
    }
    // Por fim, adicionar logout
    items.push({
        label: 'Sair',
        icon: PI.SIGN_OUT,
        command: logout,
    })
    return items;
});
const app = useApp();
watchEffect(() => {
    if (app.isHeimdall) {
        topNavItems.value = app.heimdall?.menus?.topnav ?? []
    }
})

const _topNavUserMenuItems = computed(() => {
    const items = []
    for (let i = 0; i < topNavUserMenuItems.value.length; i++) {
        const item = topNavUserMenuItems.value[i];
        const newItem = recursiveMenuItem(item)
        items.push(newItem)
    }
    return items;
})

const userMenu = ref(null);

//* Métodos
function toggleUserMenu(e) {
    userMenu.value.toggle(e);
};
function logout() {
    router.post(route('logout'));
};

</script>

<template>
    <div class="bg-primary-800">
        <Menubar :model="topNavItems"
            class="min-h-[75px] max-h-[75px] bg-primary-800 text-white border-none rounded-none max-w-7xl mx-auto"
            pt:end:class="flex gap-2">
            <template #start>
                <Link href="/">
                <LogoPMMHorizontal />
                </Link>
                <Button v-if="sidebar.state.items.length > 0 && sidebar.state.mostrarSideBarMenu" text
                    class="p-2 ml-6 rounded-full m-0 hover:bg-primary-600 cursor-pointer"
                    :class="{ 'bg-primary-600': sidebar.state.visible }" @click="sidebar.toggle">
                    <i class="pi pi-bars text-white text-xl" :class="{
                        'text-primary-500': sidebar.state.visible,
                        'text-primary-900': !sidebar.state.visible,
                    }"></i>
                </Button>
            </template>

            <template #item="{ item, props, hasSubmenu, root, index }">
                <!-- Com Navegação -->
                <!-- :class="{ 'text-primary font-semibold': isMenuActive(item) }" -->
                <Link v-if="item.href" :href="item.href" v-ripple class="flex items-center rounded-[4px]" :class="{
                    'text-white bg-primary-500': isMenuActive(item),
                    'text-black hover:text-white hover:bg-primary-500': !root,
                    'text-white bg-primary-800 hover:bg-primary-500': root && !isMenuActive(item)
                }" v-bind="props.action">
                <span v-if="item.icon" class="mr-1" :class="item.icon" />
                <span>{{ item.label }}</span>
                <i v-if="hasSubmenu"
                    :class="['pi pi-angle-down', { 'pi-angle-down ml-2': root, 'pi-angle-right ml-auto': !root }]"></i>
                </Link>
                <!-- Sem Navegação -->
                <a v-else v-ripple :class="{
                    'text-white bg-primary-500': isMenuActive(item),
                    'text-black hover:text-white hover:bg-primary-500': !root,
                    'text-white bg-primary-800 hover:bg-primary-500': root
                }" class="flex items-center rounded-[4px] hover:bg-primary-500" v-bind="props.action">
                    <span v-if="item.icon" class="mr-1" :class="item.icon" />
                    <span>{{ item.label }}</span>
                    <i v-if="hasSubmenu"
                        :class="['pi pi-angle-down', { 'pi-angle-down ml-2': root, 'pi-angle-right ml-auto': !root }]"></i>
                </a>
            </template>

            <template #button>
                <span></span>

            </template>

            <template #buttonIcon>
                <span></span>
            </template>

            <template #end>
                <!-- botão dropdown de notificações -->
                <!-- <Notifications /> -->
                <button @click="toggleUserMenu"
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
                <Menu ref="userMenu" :model="_topNavUserMenuItems" popup>
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
