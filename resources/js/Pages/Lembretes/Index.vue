<script setup>
import { computed, ref } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    lembretes: {
        type: Array,
        default: () => []
    },
    pendenciasAutomaticas: {
        type: Array,
        default: () => []
    },
    resumo: {
        type: Object,
        default: () => ({
            atrasadas: 0,
            hoje: 0,
            proximas: 0,
            automaticas: 0
        })
    },
    success: String
})

const exibindoModal = ref(false)
const grupoAberto = ref('medicacao')
const erroAcao = ref('')

const categorias = [
    { id: 'medicacao', nome: 'Medicamentos', icone: 'pi-heart', classe: 'emerald' },
    { id: 'consulta', nome: 'Consultas', icone: 'pi-user', classe: 'blue' },
    { id: 'exame', nome: 'Exames', icone: 'pi-file', classe: 'purple' },
    { id: 'vacina', nome: 'Vacinas', icone: 'pi-shield', classe: 'green' },
    { id: 'prescricao', nome: 'Prescrições', icone: 'pi-file-edit', classe: 'amber' },
    { id: 'acompanhamento', nome: 'Acompanhamentos', icone: 'pi-calendar', classe: 'cyan' },
    { id: 'outro', nome: 'Outros', icone: 'pi-bell', classe: 'slate' }
]

const form = useForm({
    titulo: '',
    descricao: '',
    tipo: 'medicacao',
    data_hora: ''
})

const pendentes = computed(() => props.lembretes.filter(
    (lembrete) => lembrete.status !== 'concluido'
))

const concluidos = computed(() => props.lembretes.filter(
    (lembrete) => lembrete.status === 'concluido'
))

const lembretesPorCategoria = computed(() => {
    const grupos = {}

    categorias.forEach((categoria) => {
        grupos[categoria.id] = pendentes.value.filter(
            (lembrete) => lembrete.tipo === categoria.id
        )
    })

    return grupos
})

const salvarLembrete = () => {
    form.post(route('lembretes.store'), {
        preserveScroll: true,
        onSuccess: () => {
            exibindoModal.value = false
            form.reset()
            form.tipo = 'medicacao'
        }
    })
}

const executarAcao = (metodo, rota, dados = {}) => {
    erroAcao.value = ''

    const opcoes = {
        preserveScroll: true,
        onError: () => {
            erroAcao.value = 'Não foi possível realizar a ação. Atualize a página e tente novamente.'
        }
    }

    if (metodo === 'delete') {
        router.delete(rota, opcoes)
        return
    }

    router[metodo](rota, dados, opcoes)
}

const concluirLembrete = (lembrete) => {
    executarAcao('patch', route('lembretes.concluir', lembrete.id))
}

const adiarLembrete = (lembrete, dias) => {
    executarAcao(
        'patch',
        route('lembretes.adiar', lembrete.id),
        { dias }
    )
}

const deletarLembrete = (lembrete) => {
    if (!window.confirm('Deseja remover somente este lembrete?')) {
        return
    }

    executarAcao('delete', route('lembretes.destroy', lembrete.id))
}

const deletarSerie = (lembrete) => {
    if (!lembrete.serie_id) {
        return
    }

    if (!window.confirm('Deseja remover todos os lembretes desta série?')) {
        return
    }

    executarAcao(
        'delete',
        route('lembretes.series.destroy', lembrete.serie_id)
    )
}

const formatarData = (dataString) => {
    if (!dataString) {
        return 'Data não informada'
    }

    return new Date(dataString).toLocaleString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const origemNome = (origem) => {
    return {
        simulador: 'Assistente',
        manual: 'Manual',
        sistema: 'Sistema'
    }[origem] || 'Manual'
}

const classeSituacao = (situacao) => {
    return {
        atrasado: 'bg-rose-100 text-rose-700',
        hoje: 'bg-amber-100 text-amber-700',
        proximo: 'bg-blue-100 text-blue-700',
        futuro: 'bg-slate-100 text-slate-600',
        concluido: 'bg-emerald-100 text-emerald-700'
    }[situacao] || 'bg-slate-100 text-slate-600'
}

const classeIcone = (classe) => {
    return {
        emerald: 'bg-emerald-50 text-emerald-600',
        blue: 'bg-blue-50 text-blue-600',
        purple: 'bg-purple-50 text-purple-600',
        green: 'bg-green-50 text-green-600',
        amber: 'bg-amber-50 text-amber-600',
        cyan: 'bg-cyan-50 text-cyan-600',
        slate: 'bg-slate-50 text-slate-600'
    }[classe]
}
</script>

<template>
    <AppLayout title="Central de Pendências">
        <div class="max-w-6xl mx-auto py-8 px-4 text-left">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-black text-slate-800">Central de Pendências</h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Lembretes pessoais e alertas automáticos dos seus dados no MedCare.
                    </p>
                </div>

                <button
                    type="button"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border-none bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white cursor-pointer hover:bg-indigo-700"
                    @click="exibindoModal = true"
                >
                    <i class="pi pi-plus-circle"></i>
                    Novo lembrete
                </button>
            </div>

            <div v-if="success" class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                {{ success }}
            </div>

            <div v-if="erroAcao" class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
                {{ erroAcao }}
            </div>

            <div class="grid grid-cols-2 gap-3 lg:grid-cols-4 mb-8">
                <div class="rounded-2xl border border-rose-100 bg-white p-4">
                    <p class="text-xs font-bold uppercase text-rose-500">Atrasadas</p>
                    <p class="mt-1 text-2xl font-black text-slate-800">{{ resumo.atrasadas }}</p>
                </div>
                <div class="rounded-2xl border border-amber-100 bg-white p-4">
                    <p class="text-xs font-bold uppercase text-amber-600">Para hoje</p>
                    <p class="mt-1 text-2xl font-black text-slate-800">{{ resumo.hoje }}</p>
                </div>
                <div class="rounded-2xl border border-blue-100 bg-white p-4">
                    <p class="text-xs font-bold uppercase text-blue-600">Próximas</p>
                    <p class="mt-1 text-2xl font-black text-slate-800">{{ resumo.proximas }}</p>
                </div>
                <div class="rounded-2xl border border-indigo-100 bg-white p-4">
                    <p class="text-xs font-bold uppercase text-indigo-600">Alertas do sistema</p>
                    <p class="mt-1 text-2xl font-black text-slate-800">{{ resumo.automaticas }}</p>
                </div>
            </div>

            <section class="mb-9">
                <div class="mb-3">
                    <h2 class="text-lg font-black text-slate-800">Pendências automáticas</h2>
                    <p class="text-xs text-slate-500">
                        Geradas a partir de vacinas, prescrições e exames ainda não revisados.
                    </p>
                </div>

                <div v-if="pendenciasAutomaticas.length === 0" class="rounded-2xl border border-slate-100 bg-white p-6 text-center text-sm text-slate-400">
                    Nenhuma pendência automática foi encontrada.
                </div>

                <div v-else class="grid gap-3 md:grid-cols-2">
                    <Link
                        v-for="pendencia in pendenciasAutomaticas"
                        :key="pendencia.id"
                        :href="pendencia.href"
                        class="rounded-2xl border border-slate-100 bg-white p-4 no-underline transition hover:border-indigo-200 hover:shadow-sm"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <span class="text-[10px] font-extrabold uppercase tracking-wide text-indigo-500">
                                    {{ pendencia.tipo }} • Sistema
                                </span>
                                <h3 class="mt-1 text-sm font-black text-slate-800">
                                    {{ pendencia.titulo }}
                                </h3>
                                <p class="mt-1 text-xs leading-relaxed text-slate-500">
                                    {{ pendencia.descricao }}
                                </p>
                                <p class="mt-2 text-[11px] font-semibold text-slate-500">
                                    {{ formatarData(pendencia.data_referencia) }}
                                </p>
                            </div>
                            <span class="shrink-0 rounded-full px-2 py-1 text-[10px] font-black" :class="classeSituacao(pendencia.situacao)">
                                {{ pendencia.rotulo_situacao }}
                            </span>
                        </div>
                    </Link>
                </div>
            </section>

            <section>
                <div class="mb-4">
                    <h2 class="text-lg font-black text-slate-800">Meus lembretes</h2>
                    <p class="text-xs text-slate-500">
                        A categoria é organizada pelo conteúdo informado no painel ou no simulador.
                    </p>
                </div>

                <div class="space-y-4">
                    <article
                        v-for="categoria in categorias"
                        :key="categoria.id"
                        class="overflow-hidden rounded-2xl border border-slate-100 bg-white"
                    >
                        <button
                            type="button"
                            class="flex w-full items-center justify-between border-none bg-white px-4 py-4 text-left cursor-pointer"
                            @click="grupoAberto = grupoAberto === categoria.id ? '' : categoria.id"
                        >
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl" :class="classeIcone(categoria.classe)">
                                    <i class="pi" :class="categoria.icone"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-black text-slate-800">{{ categoria.nome }}</p>
                                    <p class="text-xs text-slate-400">
                                        {{ lembretesPorCategoria[categoria.id]?.length || 0 }} pendente(s)
                                    </p>
                                </div>
                            </div>
                            <i class="pi text-slate-400" :class="grupoAberto === categoria.id ? 'pi-chevron-up' : 'pi-chevron-down'"></i>
                        </button>

                        <div v-if="grupoAberto === categoria.id" class="border-t border-slate-100 p-3">
                            <p v-if="!lembretesPorCategoria[categoria.id]?.length" class="px-2 py-4 text-center text-xs text-slate-400">
                                Nenhum lembrete pendente nesta categoria.
                            </p>

                            <div
                                v-for="lembrete in lembretesPorCategoria[categoria.id]"
                                :key="lembrete.id"
                                class="mb-3 rounded-xl border border-slate-100 p-4 last:mb-0"
                            >
                                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h3 class="text-sm font-black text-slate-800">{{ lembrete.titulo }}</h3>
                                            <span class="rounded-full px-2 py-1 text-[9px] font-black uppercase" :class="classeSituacao(lembrete.situacao)">
                                                {{ lembrete.rotulo_situacao }}
                                            </span>
                                            <span class="rounded-full bg-slate-100 px-2 py-1 text-[9px] font-black uppercase text-slate-500">
                                                {{ origemNome(lembrete.origem) }}
                                            </span>
                                            <span v-if="lembrete.recorrente" class="rounded-full bg-indigo-50 px-2 py-1 text-[9px] font-black uppercase text-indigo-600">
                                                Recorrente
                                            </span>
                                        </div>
                                        <p v-if="lembrete.descricao" class="mt-1 text-xs text-slate-500">
                                            {{ lembrete.descricao }}
                                        </p>
                                        <p class="mt-2 text-xs font-bold text-indigo-600">
                                            <i class="pi pi-clock mr-1 text-[10px]"></i>
                                            {{ formatarData(lembrete.data_hora) }}
                                        </p>
                                    </div>

                                    <div class="flex flex-wrap gap-2">
                                        <button type="button" class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-[11px] font-bold text-emerald-700 cursor-pointer" @click="concluirLembrete(lembrete)">
                                            Concluir
                                        </button>
                                        <button type="button" class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-[11px] font-bold text-blue-700 cursor-pointer" @click="adiarLembrete(lembrete, 1)">
                                            +1 dia
                                        </button>
                                        <button type="button" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-[11px] font-bold text-slate-600 cursor-pointer" @click="adiarLembrete(lembrete, 7)">
                                            +1 semana
                                        </button>
                                        <button type="button" class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-[11px] font-bold text-rose-700 cursor-pointer" @click="deletarLembrete(lembrete)">
                                            Excluir
                                        </button>
                                        <button v-if="lembrete.recorrente && lembrete.serie_id" type="button" class="rounded-lg border border-rose-300 bg-white px-3 py-2 text-[11px] font-bold text-rose-700 cursor-pointer" @click="deletarSerie(lembrete)">
                                            Excluir série
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>
                </div>
            </section>

            <section v-if="concluidos.length" class="mt-8 rounded-2xl border border-slate-100 bg-white p-4">
                <h2 class="text-sm font-black text-slate-800">Concluídos recentemente</h2>
                <div class="mt-3 space-y-2">
                    <div v-for="lembrete in concluidos.slice(0, 20)" :key="lembrete.id" class="flex items-center justify-between gap-3 rounded-xl bg-slate-50 px-3 py-3">
                        <div>
                            <p class="text-xs font-bold text-slate-600 line-through">{{ lembrete.titulo }}</p>
                            <p class="text-[10px] text-slate-400">{{ formatarData(lembrete.data_hora) }}</p>
                        </div>
                        <button type="button" class="border-none bg-transparent text-xs font-bold text-rose-500 cursor-pointer" @click="deletarLembrete(lembrete)">
                            Excluir
                        </button>
                    </div>
                </div>
            </section>

            <div v-if="exibindoModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4 backdrop-blur-xs">
                <div class="w-full max-w-md rounded-3xl border border-slate-100 bg-white p-6 shadow-xl">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-black text-slate-800">Novo lembrete</h3>
                            <p class="text-xs text-slate-400">Escolha a categoria ou deixe o assistente classificar pelo simulador.</p>
                        </div>
                        <button type="button" class="border-none bg-transparent text-slate-400 cursor-pointer" @click="exibindoModal = false">
                            <i class="pi pi-times"></i>
                        </button>
                    </div>

                    <form class="space-y-4" @submit.prevent="salvarLembrete">
                        <div>
                            <label class="mb-1 block text-xs font-bold text-slate-500">Título</label>
                            <input v-model="form.titulo" required type="text" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm" placeholder="Ex.: Retorno com cardiologista">
                            <p v-if="form.errors.titulo" class="mt-1 text-xs text-rose-500">{{ form.errors.titulo }}</p>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-bold text-slate-500">Categoria</label>
                            <select v-model="form.tipo" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
                                <option v-for="categoria in categorias" :key="categoria.id" :value="categoria.id">
                                    {{ categoria.nome }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-bold text-slate-500">Data e hora</label>
                            <input v-model="form.data_hora" required type="datetime-local" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
                            <p v-if="form.errors.data_hora" class="mt-1 text-xs text-rose-500">{{ form.errors.data_hora }}</p>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-bold text-slate-500">Descrição opcional</label>
                            <textarea v-model="form.descricao" class="h-20 w-full resize-none rounded-xl border border-slate-200 px-3 py-2.5 text-sm" placeholder="Informações importantes sobre o lembrete"></textarea>
                        </div>

                        <button type="submit" :disabled="form.processing" class="w-full rounded-xl border-none bg-indigo-600 py-3 text-xs font-black text-white cursor-pointer disabled:opacity-50">
                            Salvar lembrete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
