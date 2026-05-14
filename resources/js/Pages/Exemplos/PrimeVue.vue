<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import InputMask from 'primevue/inputmask';
import InputNumber from 'primevue/inputnumber';
import InputOtp from 'primevue/inputotp';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import ToggleSwitch from 'primevue/toggleswitch';
import { computed, reactive, ref } from 'vue';

//* Dados do DataTable
const dataTableData = ref([
    { id: 1, name: 'John', country: 'USA', population: 120050 },
    { id: 2, name: 'Doe', country: 'UK', population: 203250 },
    { id: 3, name: 'Sam', country: 'France', population: 300120 },
    { id: 4, name: 'Jane', country: 'Japan', population: 402300 },
]);
const totalPopulation = computed(() => {
    return dataTableData.value.reduce((acc, cur) => acc + cur.population, 0);
});

//* Dados do Formulário
const dropdownOptions = [
    { label: 'Selecione', value: null },
    { label: 'Opção 1', value: '1' },
    { label: 'Opção 2', value: '2' },
    { label: 'Opção 3', value: '3' },
];
const form = reactive({
    text: '',
    number: undefined,
    select: null,
    mask: undefined,
    pin: null,
    switch: false,
});
</script>

<template>
    <AppLayout title="Exemplo PrimeVue">
        <div class="max-w-7xl mx-auto">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-3">
                <h1 class="prose-h1 flex justify-center">
                    <a href="https://primevue.org/inputtext/"
                        class="rounded-3xl duration-300 hover:bg-green-50 hover:shadow-md hover:shadow-green-200 transition-all"
                        aria-label="PrimeVue logo">
                        <img src="/storage/primevue-logo.png">
                    </a>
                </h1>
                <p class="text-center prose-p p-3">
                    Prime Vue é uma extensa biblioteca de componentes reativos, de fácil customização e prontos para
                    produção.
                    Basta importa e utilizar. Para cada componente há vários exemplos de como utilizar e customizar na
                    documentação oficial.
                </p>


                <h1 class="text-xl ml-2">
                    Exemplos de Componentes
                </h1>
                <hr>
                <h1 class="text-lg my-1">
                    DataTable
                </h1>
                <div>
                    <DataTable :value="dataTableData" size="small">
                        <Column sortable field="id" header="ID" />
                        <Column sortable field="name" header="Nome" />
                        <Column sortable field="country" header="País" />
                        <Column sortable field="population" header="População" />
                        <template #footer>
                            <div class="w-full text-end">
                                População Total: {{ totalPopulation }}
                            </div>
                        </template>
                    </DataTable>
                </div>


                <h1 class="text-lg my-1">
                    Formulários
                </h1>
                <div class="grid grid-cols-12 gap-1">
                    <div class="col-span-4">
                        <InputText placeholder="Campo Textual" v-model="form.text" class="w-full" />
                    </div>
                    <div class="col-span-4">
                        <InputNumber show-buttons placeholder="Campo Numérico" v-model="form.number" class="w-full" />
                    </div>
                    <div class="col-span-4">
                        <Select placeholder="Campo de Seleção" v-model="form.select" :options="dropdownOptions"
                            option-label="label" option-value="value" class="w-full" input-class="w-full" />
                    </div>
                    <div class="col-span-4">
                        <InputMask placeholder="Campo com Máscara" mask="999.999.999-99" v-model="form.mask"
                            class="w-full" />
                    </div>
                    <div class="col-span-4 flex justify-center text-center">
                        <InputOtp v-model="form.pin" />
                    </div>
                    <div class="col-span-4 grid place-content-center">
                        <ToggleSwitch v-model="form.switch" />
                    </div>
                    <div class="col-span-12 grid place-content-center">
                        <pre>{{ JSON.stringify(form, null, 2) }}</pre>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
