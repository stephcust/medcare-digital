import { useApp } from "@/Assets/Composables";
import { useMediaQuery } from "@vueuse/core";
import { computed, reactive, watch } from "vue";

//* Desativar Globalmente a sidabar
const app = useApp();

/** @type {{ items: [], visible: boolean, autoReopen: boolean, mostrarSideBarMenu: boolean }} */
const sidebar = reactive({
    items: computed(() => app.heimdall?.menus?.left_sidebar ?? []),
    visible: false,
    autoReopen: false,
    mostrarSideBarMenu: computed(() => app.sidebar),
})


export function useSidebar() {
    const setItems = (items) => {
        sidebar.items = items;
    }

    const toggle = () => {
        sidebar.visible = !sidebar.visible;
    }

    return {
        state: sidebar,
        setItems,
        toggle,
    }
}

export const mquery = useMediaQuery('(max-width: 1024px)');

const menubar = reactive({
    visible: true,
})

export function useMenubar() {
    return { menubar }
}


watch(mquery, (smallWidth) => {
    if (smallWidth) {
        // sidebar.visible = false;
        // sidebar.autoReopen = true;
        menubar.visible = false;
    } else {
        menubar.visible = true;
        // if (sidebar.autoReopen) {
        // sidebar.visible = true;
        // sidebar.autoReopen = false;
        // }
    }
});
