<script setup>
import { nextTick, ref, watch } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'

const props = defineProps({
    historico: {
        type: Array,
        default: () => []
    }
})

const mensagemInicial = {
    id: 'boas-vindas',
    autor: 'assistente',
    texto: 'Olá! 👋 Sou o assistente do MedCare Digital. Você pode enviar documentos, criar lembretes, relatar atendimentos e pedir exames, receitas, vacinas, documentos do histórico clínico ou um sumário clínico em PDF.',
    hora: '09:30'
}

const montarMensagens = (historico) => [
    mensagemInicial,
    ...historico
]

const mensagens = ref(montarMensagens(props.historico))
const listaMensagens = ref(null)
const fileInput = ref(null)
const arquivoSelecionado = ref(null)

const form = useForm({
    mensagem: '',
    arquivo: null
})

const rolarParaFinal = async () => {
    await nextTick()

    if (listaMensagens.value) {
        listaMensagens.value.scrollTop = listaMensagens.value.scrollHeight
    }
}

watch(
    () => props.historico,
    (novoHistorico) => {
        mensagens.value = montarMensagens(novoHistorico)
        rolarParaFinal()
    },
    { deep: true }
)

const gatilhoArquivo = () => {
    fileInput.value?.click()
}

const handleArquivo = (event) => {
    const file = event.target.files?.[0] ?? null

    arquivoSelecionado.value = file
    form.arquivo = file
}

const removerArquivo = () => {
    arquivoSelecionado.value = null
    form.arquivo = null

    if (fileInput.value) {
        fileInput.value.value = ''
    }
}

const obterHoraAtual = () => {
    return new Date().toLocaleTimeString('pt-BR', {
        hour: '2-digit',
        minute: '2-digit'
    })
}

const mensagemPersistida = (mensagem) => {
    return Number.isInteger(Number(mensagem.id))
}

const excluirMensagem = (mensagem) => {
    if (!mensagemPersistida(mensagem)) {
        return
    }

    const confirmou = window.confirm(
        'Deseja apagar somente esta mensagem? O documento médico continuará salvo no módulo correspondente.'
    )

    if (!confirmou) {
        return
    }

    router.delete(
        route('whatsapp-simulador.mensagens.destroy', mensagem.id),
        {
            preserveScroll: true
        }
    )
}

const limparConversa = () => {
    const confirmou = window.confirm(
        'Deseja apagar todo o histórico desta conversa? Os documentos médicos e os sumários clínicos continuarão salvos nos módulos correspondentes.'
    )

    if (!confirmou) {
        return
    }

    router.delete(
        route('whatsapp-simulador.conversa.destroy'),
        {
            preserveScroll: true
        }
    )
}

const descricaoDocumento = (msg) => {
    if (msg.documento_tipo === 'receita') {
        return 'Prescrição médica'
    }

    if (msg.documento_tipo === 'vacina') {
        return 'Comprovante de vacinação'
    }

    if (msg.documento_tipo === 'historico') {
        return 'Documento do atendimento clínico'
    }

    if (msg.documento_tipo === 'sumario') {
        return 'Sumário de Preparação Clínica'
    }

    return 'Documento do exame'
}

const enviarMensagem = () => {
    if (!form.mensagem.trim() && !form.arquivo) {
        return
    }

    const textoDigitado = form.mensagem.trim()
    const nomeArquivo = form.arquivo?.name ?? null
    const idTemporario = `temporaria-${Date.now()}`

    let textoExibido = textoDigitado

    if (nomeArquivo) {
        const anexo = `📎 Arquivo: ${nomeArquivo}`
        textoExibido = textoExibido
            ? `${textoExibido}\n\n${anexo}`
            : anexo
    }

    mensagens.value.push({
        id: idTemporario,
        autor: 'usuario',
        texto: textoExibido,
        arquivo_nome: nomeArquivo,
        hora: obterHoraAtual()
    })

    rolarParaFinal()

    form.post(route('whatsapp-simulador.enviar'), {
        preserveScroll: true,
        forceFormData: true,

        onStart: () => {
            form.mensagem = ''
            form.arquivo = null
            arquivoSelecionado.value = null

            if (fileInput.value) {
                fileInput.value.value = ''
            }
        },

        onSuccess: () => {
            form.clearErrors()
        },

        onError: () => {
            mensagens.value = mensagens.value.filter(
                (mensagem) => mensagem.id !== idTemporario
            )

            mensagens.value.push({
                id: `erro-${Date.now()}`,
                autor: 'assistente',
                texto: 'Não consegui enviar sua mensagem. Tente novamente.',
                hora: obterHoraAtual()
            })

            rolarParaFinal()
        }
    })
}
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex items-center justify-center p-4 text-left">
        <Link
            href="/dashboard"
            class="fixed top-4 left-4 bg-white text-gray-700 px-4 py-2 rounded-full shadow text-sm font-semibold hover:bg-gray-100"
        >
            ← Voltar ao MedCare
        </Link>

        <div class="w-full max-w-md bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-200">
            <div class="bg-[#075E54] text-white px-4 py-3 flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white text-[#075E54] flex items-center justify-center font-bold">
                    MC
                </div>

                <div class="flex-1">
                    <h1 class="font-bold text-lg leading-tight">MedCare Digital</h1>
                    <p class="text-xs text-green-100">Conta comercial • online</p>
                </div>

                <button
                    type="button"
                    class="rounded-lg border border-white/30 px-2 py-1 text-xs font-semibold hover:bg-white/10"
                    title="Apagar toda a conversa"
                    @click="limparConversa"
                >
                    Limpar
                </button>
            </div>

            <div class="bg-[#efe7dd] h-[550px] flex flex-col">
                <div
                    ref="listaMensagens"
                    class="flex-1 overflow-y-auto p-4 space-y-3"
                >
                    <div
                        v-for="msg in mensagens"
                        :key="msg.id"
                        class="flex"
                        :class="msg.autor === 'usuario' ? 'justify-end' : 'justify-start'"
                    >
                        <div
                            class="relative max-w-[85%] rounded-2xl px-3 py-2 shadow-sm whitespace-pre-line text-sm"
                            :class="msg.autor === 'usuario'
                                ? 'bg-[#dcf8c6] rounded-br-sm'
                                : 'bg-white rounded-bl-sm'"
                        >
                            <button
                                v-if="mensagemPersistida(msg)"
                                type="button"
                                class="absolute -top-2 -right-2 flex h-6 w-6 items-center justify-center rounded-full bg-white text-xs shadow border border-gray-200 hover:bg-red-50"
                                title="Apagar esta mensagem"
                                @click="excluirMensagem(msg)"
                            >
                                🗑
                            </button>

                            <p>{{ msg.texto }}</p>

                            <div
                                v-if="msg.download_url"
                                class="mt-3 rounded-xl border border-gray-200 bg-gray-50 p-3"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center text-xl">
                                        📄
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <p class="font-semibold text-gray-800 truncate">
                                            {{ msg.arquivo_nome || 'documento.pdf' }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ descricaoDocumento(msg) }}
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-3 grid grid-cols-2 gap-2">
                                    <a
                                        v-if="msg.visualizar_url"
                                        :href="msg.visualizar_url"
                                        target="_blank"
                                        rel="noopener"
                                        class="inline-flex items-center justify-center rounded-lg border border-[#075E54] px-3 py-2 text-sm font-semibold text-[#075E54] no-underline hover:bg-green-50"
                                    >
                                        Visualizar
                                    </a>

                                    <a
                                        :href="msg.download_url"
                                        class="inline-flex items-center justify-center rounded-lg bg-[#075E54] px-3 py-2 text-sm font-semibold text-white no-underline hover:bg-[#064e46]"
                                    >
                                        Baixar
                                    </a>
                                </div>
                            </div>

                            <div class="text-[10px] text-gray-500 text-right mt-1">
                                {{ msg.hora }}
                            </div>
                        </div>
                    </div>

                    <div v-if="form.processing" class="flex justify-start">
                        <div class="bg-white rounded-2xl rounded-bl-sm px-3 py-2 shadow-sm text-sm text-gray-500">
                            MedCare está analisando sua mensagem...
                        </div>
                    </div>
                </div>

                <div
                    v-if="arquivoSelecionado"
                    class="bg-gray-200 px-4 py-2 text-xs text-gray-700 flex justify-between items-center"
                >
                    <span>
                        📎 Pronto para enviar:
                        <b>{{ arquivoSelecionado.name }}</b>
                    </span>

                    <button
                        type="button"
                        @click="removerArquivo"
                        class="text-red-500 font-bold bg-transparent border-none cursor-pointer"
                    >
                        X
                    </button>
                </div>

                <form
                    @submit.prevent="enviarMensagem"
                    class="bg-[#f0f0f0] p-3 flex items-center gap-2"
                >
                    <input
                        ref="fileInput"
                        type="file"
                        class="hidden"
                        accept="image/*,application/pdf"
                        @change="handleArquivo"
                    >

                    <button
                        type="button"
                        @click="gatilhoArquivo"
                        class="text-gray-500 hover:text-gray-700 text-xl bg-transparent border-none cursor-pointer px-1"
                    >
                        📎
                    </button>

                    <input
                        v-model="form.mensagem"
                        type="text"
                        class="flex-1 rounded-full border border-gray-300 px-4 py-2 text-sm focus:outline-none"
                        placeholder="Mensagem ou descrição do arquivo..."
                        :disabled="form.processing"
                    >

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-10 h-10 rounded-full bg-[#25D366] text-white flex items-center justify-center font-bold border-none cursor-pointer disabled:opacity-50"
                    >
                        ➤
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>
