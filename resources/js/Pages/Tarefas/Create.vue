<script setup>
import { formCreate } from "@/Components/Tarefas/TarefaForm";
import FormTarefa from "@/Components/Tarefas/FormTarefa.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import Button from "primevue/button";
import Card from "primevue/card";

const props = defineProps(["tarefa"]);

function submitForm() {
    formCreate.post(route("tarefa.store"), {
        onSuccess: () => {
            formCreate.reset();
        },
    });
}
</script>

<template>
    <AppLayout :has-back-button="true" :route-back-button="route('tarefa.index')">
        <Card class="w-full">
            <template #content>
                <form @submit.prevent="submitForm">
                    <FormTarefa :form="formCreate" />
                    <div class="mt-6 text-center">
                        <Button icon="pi pi-save" label="Salvar" :loading="formCreate.processing" type="submit" />
                    </div>
                </form>
            </template>
        </Card>
    </AppLayout>
</template>
