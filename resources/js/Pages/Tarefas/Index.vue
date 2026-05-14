<script setup>
import { formatData } from "@/Assets/DateFunctions";
import { formatPrioridade } from "@/Components/Tarefas/TarefaFunctions";
import { tarefaSelecionada, visibleDelete } from "@/Components/Tarefas/TarefaState";
import Delete from "@/Components/Tarefas/Delete.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link, router } from "@inertiajs/vue3";
import Button from "primevue/button";
import Card from "primevue/card";
import Column from "primevue/column";
import DataTable from "primevue/datatable";
import ProgressSpinner from "primevue/progressspinner";
import { useToast } from "primevue/usetoast";
import { ref } from "vue";

const props = defineProps(["tarefas"]);
const toast = useToast();
const loadingConcluir = ref(false);

function abrirModalDelete(tarefa) {
    tarefaSelecionada.value = tarefa;
    visibleDelete.value = true
}

//* Métodos
function concluirTarefa(tarefa) {
    const id = tarefa.id;
    loadingConcluir.value = true;
    router.post(route("tarefa.complete", { id }), null, {
        onSuccess: (r) => {
            toast.add({
                severity: "success",
                summary: "Concluído com sucesso",
                detail: "Tarefa",
                life: 5000,
            });
        },
        onError: (e) => {
            toast.add({
                severity: "error",
                summary: "Erro ao concluir",
                detail: "Tarefa",
                life: 5000,
            });
        },
        onFinish: () => loadingConcluir.value = false,
    });
}
</script>

<template>
    <AppLayout :has-create-button="true" :route-create-button="route('tarefa.create')">
        <Card class="w-full">
            <template #content>
                <div class="flex justify-between">
                    <div class="text-2xl">Minhas Tarefas</div>
                </div>
                <span class="text-lg text-gray-500">Exemplo de CRUD: Listagem de tarefas</span>
                <p class="m-0" v-if="!tarefas?.length">
                    Você ainda não tem nenhuma tarefa. As suas tarefas serão listadas
                    abaixo. Adicione uma nova tarefa no botão acima "Nova Tarefa".
                </p>
                <DataTable v-else :value="props.tarefas" class="rounded border" size="small" removable-sort>
                    <Column field="id" sortable header="ID" />
                    <Column field="titulo" sortable header="Título" />
                    <Column field="descricao" sortable header="Descrição" />
                    <Column :field="(v) => formatPrioridade(v.prioridade)" sortable header="Prioridade" />
                    <Column :field="(v) => formatData(v.created_at)" sortField="created_at" sortable
                        header="Criado Em" />
                    <Column :field="(v) => formatData(v.concluida_em)" sortField="concluida_em" sortable
                        header="Concluida Em" />
                    <Column header="Ações">
                        <template #body="{ data }">
                            <div class="flex gap-1">
                                <Button icon="pi pi-check" :disabled="data.concluida_em" class="p-1"
                                    :class="{ 'text-gray-400': data.concluida_em }" severity="success"
                                    v-tooltip.top="'Concluir'" @click="concluirTarefa(data)" text
                                    :loading="loadingConcluir" />
                                <Link :href="route('tarefa.edit', data.id)">
                                <Button icon="pi pi-pencil" severity="warn" class="p-1" text v-tooltip.top="'Editar'" />
                                </Link>
                                <Link :href="route('tarefa.show', data.id)">
                                <Button icon="pi pi-eye" severity="info" class="p-1" text v-tooltip.top="'Ver'" />
                                </Link>
                                <Button icon="pi pi-trash" severity="danger" class="p-1" text v-tooltip.top="'Apagar'"
                                    @click="abrirModalDelete(data)" />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </template>
        </Card>
        <Delete :tarefa="tarefaSelecionada" />
    </AppLayout>
</template>
