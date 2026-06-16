<script setup>
import { ref } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
    respostaIA: String
})

const mensagens = ref([
    {
        autor: 'assistente',
        texto: 'Olá! 👋 Sou o assistente do MedCare Digital. Agora você também pode me enviar imagens ou documentos de exames, receitas e vacinas que eu extraio os dados para você!',
        hora: '09:30'
    }
])

const fileInput = ref(null)
const arquivoSelecionado = ref(null)

const form = useForm({
    mensagem: '',
    arquivo: null // Novo campo para o arquivo físico
})

const obterHoraAtual = () => {
    const agora = new Date()
    return agora.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
}

const gatilhoArquivo = () => {
    fileInput.value.click()
}

const handleArquivo = (e) => {
    const file = e.target.files[0]
    if (file) {
        arquivoSelecionado.value = file
        form.arquivo = file
    }
}

const enviarMensagem = () => {
    if (!form.mensagem.trim() && !form.arquivo) return

    const textoUsuario = form.mensagem
    const nomeArquivo = form.arquivo ? form.arquivo.name : null

    // Adiciona a mensagem do usuário na tela de chat
    mensagens.value.push({
        autor: 'usuario',
        texto: textoUsuario + (nomeArquivo ? `\n\n📁 [Arquivo Anexo: ${nomeArquivo}]` : ''),
        hora: obterHoraAtual()
    })

    // Dispara via Inertia (o useForm converte automaticamente para Multipart se houver arquivo)
    form.post(route('whatsapp-simulador.enviar'), {
        preserveScroll: true,
        onStart: () => {
            form.mensagem = ''
            form.arquivo = null
            arquivoSelecionado.value = null
        },
        onSuccess: () => {
            if (props.respostaIA) {
                mensagens.value.push({
                    autor: 'assistente',
                    texto: props.respostaIA,
                    hora: obterHoraAtual()
                })
            }
        },
        onError: () => {
            mensagens.value.push({
                autor: 'assistente',
                texto: 'Houve um erro ao processar o seu arquivo. Certifique-se de que a IA está ativa.',
                hora: obterHoraAtual()
            })
        }
    })
}
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex items-center justify-center p-4 text-left">
        <Link href="/dashboard" class="fixed top-4 left-4 bg-white text-gray-700 px-4 py-2 rounded-full shadow text-sm font-semibold hover:bg-gray-100">
            ← Voltar ao MedCare
        </Link>
        <div class="w-full max-w-md bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-200">
            <div class="bg-[#075E54] text-white px-4 py-3 flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white text-[#075E54] flex items-center justify-center font-bold">MC</div>
                <div class="flex-1">
                    <h1 class="font-bold text-lg leading-tight">MedCare Digital</h1>
                    <p class="text-xs text-green-100">Conta comercial • online</p>
                </div>
            </div>

            <div class="bg-[#efe7dd] h-[550px] flex flex-col">
                <div class="flex-1 overflow-y-auto p-4 space-y-3">
                    <div v-for="(msg, index) in mensagens" :key="index" class="flex" :class="msg.autor === 'usuario' ? 'justify-end' : 'justify-start'">
                        <div class="max-w-[85%] rounded-2xl px-3 py-2 shadow-sm whitespace-pre-line text-sm" :class="msg.autor === 'usuario' ? 'bg-[#dcf8c6] rounded-br-sm' : 'bg-white rounded-bl-sm'">
                            <p>{{ msg.texto }}</p>
                            <div class="text-[10px] text-gray-500 text-right mt-1">{{ msg.hora }}</div>
                        </div>
                    </div>

                    <div v-if="form.processing" class="flex justify-start">
                        <div class="bg-white rounded-2xl rounded-bl-sm px-3 py-2 shadow-sm text-sm text-gray-500">
                            MedCare está analisando o seu documento...
                        </div>
                    </div>
                </div>

                <div v-if="arquivoSelecionado" class="bg-gray-200 px-4 py-2 text-xs text-gray-700 flex justify-between items-center">
                    <span>📎 Pronto para enviar: <b>{{ arquivoSelecionado.name }}</b></span>
                    <button @click="arquivoSelecionado = null; form.arquivo = null" class="text-red-500 font-bold bg-transparent border-none cursor-pointer">X</button>
                </div>

                <form @submit.prevent="enviarMensagem" class="bg-[#f0f0f0] p-3 flex items-center gap-2">
                    <input type="file" ref="fileInput" class="hidden" accept="image/*,application/pdf" @change="handleArquivo" />
                    
                    <button type="button" @click="gatilhoArquivo" class="text-gray-500 hover:text-gray-700 text-xl bg-transparent border-none cursor-pointer px-1">
                        📎
                    </button>

                    <input v-model="form.mensagem" type="text" class="flex-1 rounded-full border border-gray-300 px-4 py-2 text-sm focus:outline-none" placeholder="Mensagem ou descrição do arquivo..." :disabled="form.processing"/>
                    <button type="submit" :disabled="form.processing" class="w-10 h-10 rounded-full bg-[#25D366] text-white flex items-center justify-center font-bold border-none cursor-pointer">➤</button>
                </form>
            </div>
        </div>
    </div>
</template>