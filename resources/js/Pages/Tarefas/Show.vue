<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from 'primevue/card';

const props = defineProps(['tarefa']);

const optionsPrioridade = [
    { value: 0, label: 'Baixa' },
    { value: 1, label: 'Média' },
    { value: 2, label: 'Alta' },
];

function getPrioridade(p) {
    return optionsPrioridade.find(o => o.value === p)?.label;
}
</script>

<template>
    <AppLayout :has-back-button="true" :route-back-button="route('tarefa.index')">
        <Card class="w-full">
            <template #content>
                <div class="grid grid-cols-3 gap-3">
                    <u>Título</u>
                    <span class="col-span-2">{{ props.tarefa?.titulo }}</span>
                    <u>Descrição</u>
                    <span class="col-span-2">{{ props.tarefa?.descricao }}</span>
                    <u>Prioridade</u>
                    <span class="col-span-2">{{ getPrioridade(props.tarefa?.prioridade) }}</span>
                </div>
            </template>
        </Card>
    </AppLayout>
</template>
