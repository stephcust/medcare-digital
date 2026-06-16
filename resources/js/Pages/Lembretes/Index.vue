<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    lembretes: Array
});

const exibindoModal = ref(false);

const form = useForm({
    titulo: '',
    descricao: '',
    tipo: 'medicamento',
    data_hora: ''
});

const salvarLembrete = () => {
    form.post(route('lembretes.store'), {
        onSuccess: () => {
            exibindoModal.value = false;
            form.reset();
        }
    });
};

const deletarLembrete = (id) => {
    if (confirm('Deseja remover este lembrete?')) {
        router.delete(route('lembretes.destroy', id));
    }
};

const formatarData = (dataString) => {
    return new Date(dataString).toLocaleString('pt-BR', {
        day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit'
    });
};
</script>

<template>
    <AppLayout title="Lembretes & Consultas">
        <div class="max-w-4xl mx-auto py-8 px-4">

            <!-- Botão de Criação Manual Rápida -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-xl font-bold text-slate-800">Meus Lembretes Ativos</h2>
                    <p class="text-xs text-slate-400">Criados via painel web ou extraídos pelo assistente do WhatsApp.</p>
                </div>
                <button @click="exibindoModal = true" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-xl text-sm transition border-none cursor-pointer flex items-center gap-2">
                    <i class="pi pi-plus-circle"></i> Novo Lembrete
                </button>
            </div>

            <!-- Lista de Lembretes -->
            <div v-if="lembretes.length === 0" class="bg-white p-8 rounded-2xl border border-slate-100 text-center text-slate-400 text-sm">
                Nenhum lembrete agendado para os próximos dias.
            </div>

            <div v-else class="space-y-3">
                <div v-for="lembrete in lembretes" :key="lembrete.id" class="bg-white p-4 rounded-2xl border border-slate-100 flex justify-between items-center shadow-xs">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center" :class="{
                            'bg-emerald-50 text-emerald-600': lembrete.tipo === 'medicamento',
                            'bg-blue-50 text-blue-600': lembrete.tipo === 'consulta',
                            'bg-purple-50 text-purple-600': lembrete.tipo === 'exame',
                            'bg-slate-50 text-slate-600': lembrete.tipo === 'outros',
                        }">
                            <i class="pi" :class="{
                                'pi-calendar-plus': lembrete.tipo === 'medicamento',
                                'pi-user': lembrete.tipo === 'consulta',
                                'pi-file-pdf': lembrete.tipo === 'exame',
                                'pi-bell': lembrete.tipo === 'outros',
                            }"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                                {{ lembrete.titulo }}
                                <span class="text-[9px] font-extrabold uppercase px-1.5 py-0.5 rounded-sm" :class="lembrete.origem === 'whatsapp' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600'">
                                    {{ lembrete.origem }}
                                </span>
                            </h4>
                            <p class="text-xs text-slate-400 mt-0.5">{{ lembrete.descricao }}</p>
                            <span class="text-[11px] font-semibold text-indigo-600 block mt-1">
                                <i class="pi pi-clock text-[10px]"></i> {{ formatarData(lembrete.data_hora) }}
                            </span>
                        </div>
                    </div>
                    <button @click="deletarLembrete(lembrete.id)" class="text-slate-300 hover:text-rose-500 bg-transparent border-none cursor-pointer p-2">
                        <i class="pi pi-trash"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Formulário Manual -->
            <div v-if="exibindoModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 z-50">
                <div class="bg-white rounded-3xl p-6 max-w-md w-full border border-slate-100 shadow-xl text-left">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-base font-bold text-slate-800">Agendar Novo Alerta</h3>
                        <button @click="exibindoModal = false" class="text-slate-400 hover:text-slate-600 bg-transparent border-none cursor-pointer"><i class="pi pi-times"></i></button>
                    </div>

                    <form @submit.prevent="salvarLembrete" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1">Título</label>
                            <input v-model="form.titulo" type="text" placeholder="Ex: Tomar Amoxicilina, Retorno Cardio" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm" required />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1">Tipo de Alerta</label>
                            <select v-model="form.tipo" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm">
                                <option value="medicamento">Medicamento / Remédio</option>
                                <option value="consulta">Consulta Médica</option>
                                <option value="exame">Exame Agendado</option>
                                <option value="outros">Outros compromissos</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1">Data e Hora</label>
                            <input v-model="form.data_hora" type="datetime-local" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm" required />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1">Instruções / Notas (Opcional)</label>
                            <textarea v-model="form.descricao" placeholder="Ex: Tomar em jejum com água" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm h-20 resize-none"></textarea>
                        </div>
                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-xl text-xs transition border-none cursor-pointer" :disabled="form.processing">
                            Salvar no Prontuário
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
