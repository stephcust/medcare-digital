<script setup>
import InputText from "primevue/inputtext";
import Select from "primevue/select";
import Textarea from "primevue/textarea";
import { optionsPrioridade } from "@/Components/Tarefas/TarefaList";
import { hasErrors } from "@/Assets/Functions";

const props = defineProps({
    form: {
        Type: Object,
        required: true,
    },
});
</script>

<template>
    <div>
        <label for="titulo">Título <span class="text-red-400">*</span> </label>
        <InputText placeholder="Nome da Tarefa" type="text" v-model="props.form.titulo" id="titulo" class="w-full"
            :class="hasErrors(props.form, 'titulo') ? `border border-red-500` : ''" />
        <span v-if="hasErrors(props.form, 'titulo')" class="text-red-500">
            {{ props.form.errors?.titulo }}
        </span>
    </div>
    <div>
        <label for="descricao">Descrição</label>
        <Textarea placeholder="Descreva.." v-model="props.form.descricao" id="descricao" rows="3" class="w-full" />
    </div>
    <div>
        <label for="prioridade">Prioridade</label>
        <Select v-model="props.form.prioridade" id="prioridade" :options="optionsPrioridade" option-label="label"
            option-value="value" class="w-full" />
    </div>
</template>
