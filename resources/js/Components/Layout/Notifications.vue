<script setup>
import Menu from 'primevue/menu';
import { ref } from 'vue';


const notificatioMenu = ref(null);
const items = ['item'];

//* Métodos
function toglleNotifications(e) {
    notificatioMenu.value.toggle(e);
};
</script>

<template>
    <button @click="toglleNotifications"
        class="rounded-md p-3 grid place-content-center text-white hover:bg-primary-500 focus:outline-none focus:bg-primary-500 active:bg-primary-500">
        <i class="pi pi-bell text-xl"></i>
    </button>
    <Menu ref="notificatioMenu" :model="items" popup>
        <template #item>
            japisjdipsajdipajdips
            <div class="min-h-[300px]">
                doahdosuhdouah
            </div>
        </template>
    </Menu>
</template>
