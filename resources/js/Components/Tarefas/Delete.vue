<script setup>
import { formatData } from "@/Assets/DateFunctions";
import { formDelete } from "@/Components/Tarefas/TarefaForm";
import { formatPrioridade } from "@/Components/Tarefas/TarefaFunctions";
import { visibleDelete } from "@/Components/Tarefas/TarefaState";
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import { useToast } from "primevue/usetoast";
import { watch } from "vue";

const props = defineProps({
    tarefa: {
        Type: Object,
        required: true,
    },
});

const toast = useToast();

function apagarTarefa() {
    formDelete.delete(route("tarefa.destroy", { tarefa: formDelete.id }), {
        onSuccess: () => {
            toast.add({
                severity: "success",
                summary: "Excluído com sucesso!",
                detail: "Tarefa",
                life: 5000
            });
            formDelete.reset();
            visibleDelete.value = false;
        },
        onError: () => {
            toast.add({
                severity: "error",
                summary: "Erro ao concluir tarefa",
                life: 5000,
            });
        },
    });
}

watch(
    () => props.tarefa,
    (newValue) => {
        formDelete.defaults(newValue);
        formDelete.reset();
    }
);
</script>

<template>
    <Dialog v-model:visible="visibleDelete" modal header="Exclusão de Tarefa" :style="{ width: '25rem' }" position="top"
        :draggable="false">
        <div class="text-surface-500 dark:text-surface-400 block mb-8">
            Deseja realmente excluir esta tarefa?
            <div class="grid grid-cols-4 gap-1 my-4 px-3">
                <div class="col-span-2 text-surface-300">
                    Prioridade
                </div>
                <div class="col-span-2 font-semibold">
                    {{ formatPrioridade(tarefa.prioridade) }}
                </div>
                <div class="col-span-2 text-surface-300">
                    Título
                </div>
                <div class="col-span-2 font-semibold">
                    {{ tarefa.titulo }}
                </div>
                <div class="col-span-2 text-surface-300">
                    Descrição
                </div>
                <div class="col-span-2 font-semibold">
                    {{ tarefa.descricao }}
                </div>
                <div class="col-span-2 text-surface-300">
                    Criado Em
                </div>
                <div class="col-span-2 font-semibold">
                    {{ formatData(tarefa.created_at) }}
                </div>
                <div class="col-span-2 text-surface-300">
                    Concluída Em
                </div>
                <div class="col-span-2 font-semibold">
                    {{ formatData(tarefa.concluida_em) }}
                </div>
            </div>
        </div>
        <div class="flex justify-end gap-2">
            <Button type="button" label="Cancelar" severity="secondary" @click="visibleDelete = false"></Button>
            <Button icon="pi pi-trash" type="button" label="Confirmar" severity="danger"
                :loading="formDelete.processing" @click="apagarTarefa"></Button>
        </div>
    </Dialog>
</template>
