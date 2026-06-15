<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { Link } from '@inertiajs/vue3'

const mensagem = ref('')
const carregando = ref(false)

const mensagens = ref([
    {
        autor: 'assistente',
        texto: 'Olá! 👋 Sou o assistente do MedCare Digital. Posso te ajudar com resumo da saúde, vacinas, preparo para consultas, dados do plano e guia médico.\n\nLembrete: minhas orientações não substituem atendimento médico profissional.',
        hora: '09:30'
    }
])

const obterHoraAtual = () => {
    const agora = new Date()

    return agora.toLocaleTimeString('pt-BR', {
        hour: '2-digit',
        minute: '2-digit'
    })
}

const enviarMensagem = async () => {
    if (!mensagem.value.trim()) {
        return
    }

    const textoUsuario = mensagem.value

    mensagens.value.push({
        autor: 'usuario',
        texto: textoUsuario,
        hora: obterHoraAtual()
    })

    mensagem.value = ''
    carregando.value = true

    try {
        const response = await axios.post(route('whatsapp-simulador.enviar'), {
            mensagem: textoUsuario
        })

        mensagens.value.push({
            autor: 'assistente',
            texto: response.data.resposta,
            hora: obterHoraAtual()
        })
    } catch (error) {
        mensagens.value.push({
            autor: 'assistente',
            texto: 'Não consegui responder agora. Tente novamente em alguns instantes.',
            hora: obterHoraAtual()
        })
    } finally {
        carregando.value = false
    }
}
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex items-center justify-center p-4">
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
                    <h1 class="font-bold text-lg leading-tight">
                        MedCare Digital
                    </h1>
                    <p class="text-xs text-green-100">
                        Conta comercial • online
                    </p>
                </div>

                <div class="text-xl">
                    ⋮
                </div>
            </div>

            <div class="bg-[#efe7dd] h-[600px] flex flex-col">
                <div class="flex-1 overflow-y-auto p-4 space-y-3">
                    <div
                        v-for="(msg, index) in mensagens"
                        :key="index"
                        class="flex"
                        :class="msg.autor === 'usuario' ? 'justify-end' : 'justify-start'"
                    >
                        <div
                            class="max-w-[85%] rounded-2xl px-3 py-2 shadow-sm whitespace-pre-line text-sm"
                            :class="msg.autor === 'usuario'
                                ? 'bg-[#dcf8c6] rounded-br-sm'
                                : 'bg-white rounded-bl-sm'"
                        >
                            <p>{{ msg.texto }}</p>

                            <div class="text-[10px] text-gray-500 text-right mt-1">
                                {{ msg.hora }}
                                <span v-if="msg.autor === 'usuario'" class="text-blue-500 ml-1">✓✓</span>
                            </div>
                        </div>
                    </div>

                    <div v-if="carregando" class="flex justify-start">
                        <div class="bg-white rounded-2xl rounded-bl-sm px-3 py-2 shadow-sm text-sm text-gray-500">
                            MedCare está digitando...
                        </div>
                    </div>
                </div>

                <form
                    @submit.prevent="enviarMensagem"
                    class="bg-[#f0f0f0] p-3 flex items-center gap-2"
                >
                    <input
                        v-model="mensagem"
                        type="text"
                        class="flex-1 rounded-full border border-gray-300 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-600"
                        placeholder="Mensagem"
                    />

                    <button
                        type="submit"
                        :disabled="carregando"
                        class="w-10 h-10 rounded-full bg-[#25D366] text-white flex items-center justify-center font-bold disabled:opacity-50"
                    >
                        ➤
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>