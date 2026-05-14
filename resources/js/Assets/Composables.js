import { usePage } from "@inertiajs/vue3";
import { computed, reactive, version } from "vue";

/**
 * Dados da aplicação e do usuário autenticado - `Não desestruturar, pois perde reatividade`
 */
export function useApp() {
    const page = usePage()
    const data = reactive({
        appName: computed(/** @returns {string} */() => page.props.appName ?? null),
        appVersion: computed(/** @returns {string} */() => page.props.appVersion ?? null),
        appEnv: computed(/** @returns {G.AppEnv} */() => page.props.env ?? null),
        isProduction: computed(/** @returns {boolean} */() => page.props.isProduction),
        isHeimdall: computed(/** @returns {boolean} */() => page.props.isHeimdall),
        errors: computed(() => page.props.errors ?? {}),
        user: computed(/** @returns {M.User} */() => page.props.auth?.user ?? null),
        permissions: computed(/** @returns {M.Permission} */() => page.props.auth?.permissions ?? null),
        phpVersion: computed(/** @returns {string} */() => page.props.phpVersion ?? null),
        laravelVersion: computed(/** @returns {string} */() => page.props.laravelVersion ?? null),
        jetstream: computed(/** @returns {G.JetStreamProp} */() => page.props.jetstream),
        heimdall: computed(/** @returns {G.Heimdall.Prop} */() => page.props.heimdall),
        breadcrumbs: computed(/** @returns {G.Breadcrumbs} */() => page.props.breadcrumbs ?? []),
        sidebar: computed(/** @returns {boolean} */() => page.props.useSidebar),
        vueVersion: version,
    })
    return data
}

/** @import * as G from "@/Assets/GlobalTypes" */
/** @import * as M from "@/Assets/ModelTypes" */
