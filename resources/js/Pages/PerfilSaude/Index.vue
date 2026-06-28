<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { useForm } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
    perfil: {
        type: Object,
        required: true,
    },
})

const listaParaTexto = (valor) => Array.isArray(valor)
    ? valor.join('\n')
    : ''

const textoParaLista = (valor) => String(valor ?? '')
    .split(/\n|,|;/)
    .map((item) => item.trim())
    .filter(Boolean)

const form = useForm({
    data_nascimento: props.perfil.data_nascimento ?? '',
    genero: props.perfil.genero ?? '',
    telefone: props.perfil.telefone ?? '',
    tipo_sanguineo: props.perfil.tipo_sanguineo ?? '',
    peso_kg: props.perfil.peso_kg ?? '',
    altura_cm: props.perfil.altura_cm ?? '',
    alergias_texto: listaParaTexto(props.perfil.alergias),
    condicoes_cronicas_texto: listaParaTexto(props.perfil.condicoes_cronicas),
    medicamentos_continuos_texto: listaParaTexto(props.perfil.medicamentos_continuos),
    cirurgias_anteriores_texto: listaParaTexto(props.perfil.cirurgias_anteriores),
    dispositivos_implantes_texto: listaParaTexto(props.perfil.dispositivos_implantes),
    observacoes_importantes: props.perfil.observacoes_importantes ?? '',
    contato_emergencia_nome: props.perfil.contato_emergencia_nome ?? '',
    contato_emergencia_telefone: props.perfil.contato_emergencia_telefone ?? '',
    contato_emergencia_parentesco: props.perfil.contato_emergencia_parentesco ?? '',
})

const idadeCalculada = computed(() => {
    if (!form.data_nascimento) return null

    const nascimento = new Date(`${form.data_nascimento}T00:00:00`)
    const hoje = new Date()

    if (Number.isNaN(nascimento.getTime())) return null

    let idade = hoje.getFullYear() - nascimento.getFullYear()
    const diferencaMes = hoje.getMonth() - nascimento.getMonth()

    if (
        diferencaMes < 0
        || (diferencaMes === 0 && hoje.getDate() < nascimento.getDate())
    ) {
        idade -= 1
    }

    return idade >= 0 ? idade : null
})

const salvar = () => {
    form.transform((dados) => ({
        data_nascimento: dados.data_nascimento,
        genero: dados.genero || null,
        telefone: dados.telefone || null,
        tipo_sanguineo: dados.tipo_sanguineo || null,
        peso_kg: dados.peso_kg === '' ? null : Number(dados.peso_kg),
        altura_cm: dados.altura_cm === '' ? null : Number(dados.altura_cm),
        alergias: textoParaLista(dados.alergias_texto),
        condicoes_cronicas: textoParaLista(dados.condicoes_cronicas_texto),
        medicamentos_continuos: textoParaLista(dados.medicamentos_continuos_texto),
        cirurgias_anteriores: textoParaLista(dados.cirurgias_anteriores_texto),
        dispositivos_implantes: textoParaLista(dados.dispositivos_implantes_texto),
        observacoes_importantes: dados.observacoes_importantes || null,
        contato_emergencia_nome: dados.contato_emergencia_nome || null,
        contato_emergencia_telefone: dados.contato_emergencia_telefone || null,
        contato_emergencia_parentesco: dados.contato_emergencia_parentesco || null,
    })).put(route('perfil-saude.update'), {
        preserveScroll: true,
    })
}
</script>

<template>
    <AppLayout title="Perfil de Saúde">
        <div class="min-h-screen bg-slate-50 py-10 text-left">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="mb-7 rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <span class="rounded-full bg-indigo-50 px-3 py-1 text-[11px] font-black uppercase tracking-wider text-indigo-600">
                                Dados atualizáveis
                            </span>
                            <h1 class="mt-3 text-2xl font-black tracking-tight text-slate-900">
                                Meu Perfil de Saúde
                            </h1>
                            <p class="mt-1 max-w-3xl text-sm leading-relaxed text-slate-500">
                                Mantenha as informações que podem ser importantes em consultas e atendimentos. Esses dados serão usados nos sumários clínicos somente quando você selecionar a seção de perfil.
                            </p>
                        </div>

                        <div class="rounded-2xl border border-indigo-100 bg-indigo-50 px-4 py-3 text-sm text-indigo-800">
                            <p class="font-bold">{{ perfil.nome }}</p>
                            <p class="text-xs text-indigo-500">{{ perfil.email }}</p>
                            <p v-if="perfil.atualizado_em" class="mt-1 text-[11px] text-indigo-400">
                                Atualizado em {{ perfil.atualizado_em }}
                            </p>
                        </div>
                    </div>
                </div>

                <form class="space-y-6" @submit.prevent="salvar">
                    <section class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
                        <h2 class="text-base font-black text-slate-900">Identificação e medidas</h2>
                        <p class="mt-1 text-xs text-slate-500">A idade é calculada automaticamente pela data de nascimento.</p>

                        <div class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3">
                            <div>
                                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">Data de nascimento *</label>
                                <input v-model="form.data_nascimento" type="date" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm" required>
                                <p v-if="form.errors.data_nascimento" class="mt-1 text-xs text-rose-600">{{ form.errors.data_nascimento }}</p>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">Idade</label>
                                <div class="rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                                    {{ idadeCalculada !== null ? `${idadeCalculada} anos` : 'Preencha a data de nascimento' }}
                                </div>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">Gênero</label>
                                <select v-model="form.genero" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm">
                                    <option value="">Não informar</option>
                                    <option value="Feminino">Feminino</option>
                                    <option value="Masculino">Masculino</option>
                                    <option value="Outro">Outro</option>
                                    <option value="Prefiro não informar">Prefiro não informar</option>
                                </select>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">Telefone</label>
                                <input v-model="form.telefone" type="text" placeholder="(92) 99999-9999" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm">
                            </div>

                            <div>
                                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">Tipo sanguíneo</label>
                                <select v-model="form.tipo_sanguineo" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm">
                                    <option value="">Não informado</option>
                                    <option v-for="tipo in ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']" :key="tipo" :value="tipo">
                                        {{ tipo }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">Peso atual (kg)</label>
                                <input v-model="form.peso_kg" type="number" min="1" max="500" step="0.1" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm">
                                <p v-if="perfil.peso_atualizado_em" class="mt-1 text-[11px] text-slate-400">Última atualização: {{ perfil.peso_atualizado_em }}</p>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">Altura (cm)</label>
                                <input v-model="form.altura_cm" type="number" min="30" max="250" step="1" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm">
                            </div>
                        </div>
                    </section>

                    <section class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
                        <h2 class="text-base font-black text-slate-900">Informações clínicas importantes</h2>
                        <p class="mt-1 text-xs text-slate-500">Digite um item por linha. Essas informações são declarações do próprio paciente.</p>

                        <div class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">Alergias conhecidas</label>
                                <textarea v-model="form.alergias_texto" rows="5" placeholder="Ex.: Dipirona&#10;Amoxicilina" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"></textarea>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">Condições crônicas</label>
                                <textarea v-model="form.condicoes_cronicas_texto" rows="5" placeholder="Ex.: Asma&#10;Hipertensão" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"></textarea>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">Medicamentos de uso contínuo</label>
                                <textarea v-model="form.medicamentos_continuos_texto" rows="5" placeholder="Informe nome, dose e frequência quando souber" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"></textarea>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">Cirurgias anteriores</label>
                                <textarea v-model="form.cirurgias_anteriores_texto" rows="5" placeholder="Ex.: Cirurgia no joelho em 2024" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"></textarea>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">Dispositivos ou implantes</label>
                                <textarea v-model="form.dispositivos_implantes_texto" rows="5" placeholder="Ex.: Marcapasso, prótese, DIU" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"></textarea>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">Outras informações importantes</label>
                                <textarea v-model="form.observacoes_importantes" rows="5" placeholder="Algo que o profissional de saúde deve saber" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"></textarea>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
                        <h2 class="text-base font-black text-slate-900">Contato de emergência</h2>

                        <div class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-3">
                            <div>
                                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">Nome</label>
                                <input v-model="form.contato_emergencia_nome" type="text" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm">
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">Telefone</label>
                                <input v-model="form.contato_emergencia_telefone" type="text" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm">
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">Parentesco ou relação</label>
                                <input v-model="form.contato_emergencia_parentesco" type="text" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm">
                            </div>
                        </div>
                    </section>

                    <div class="flex flex-col gap-3 rounded-3xl border border-indigo-100 bg-indigo-50 p-5 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-xs leading-relaxed text-indigo-700">
                            O MedCare não confirma diagnósticos. Revise seus dados sempre que houver alguma mudança.
                        </p>
                        <button type="submit" :disabled="form.processing" class="rounded-2xl border-none bg-indigo-600 px-6 py-3 text-sm font-black text-white shadow-sm transition hover:bg-indigo-700 disabled:opacity-60">
                            {{ form.processing ? 'Salvando...' : 'Salvar Perfil de Saúde' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
