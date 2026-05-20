<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const propriedades = defineProps({
  paciente: Object,
  vacinacoes: Array
});

const abrirModal = ref(false);

const formulario = useForm({
  nome_vacina: '',
  numero_dose: '1ª Dose',
  lote: '',
  fabricante: '',
  data_aplicacao: new Date().toISOString().substr(0, 10), // Define o dia atual como padrão
  data_proxima_dose: '',
  observacoes: ''
});

const enviarFormulario = () => {
  formulario.post(route('pacientes.vacinacoes.salvar', propriedades.paciente.id), {
    onSuccess: () => {
      abrirModal.value = false;
      formulario.reset();
    }
  });
};

const removerRegistro = (id) => {
  if (confirm('Deseja realmente excluir em definitivo este registro de vacinação?')) {
    formulario.delete(route('vacinacoes.excluir', id));
  }
};

const formatarData = (stringData) => {
  if (!stringData) return '';
  const data = new Date(stringData);
  return data.toLocaleDateString('pt-BR', { timeZone: 'UTC' });
};
</script>

<template>
  <div class="p-6 max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6 bg-white p-4 rounded-lg shadow-sm">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Caderneta de Vacinação</h1>
        <p class="text-sm text-gray-600">Paciente: <span class="font-semibold text-indigo-600">{{ paciente.name }}</span></p>
      </div>
      <button @click="abrirModal = true" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md shadow transition">
        + Registrar Vacina
      </button>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
        <thead class="bg-gray-50 text-gray-700 uppercase text-xs font-semibold">
          <tr>
            <th class="px-6 py-3">Vacina / Imunizante</th>
            <th class="px-6 py-3">Dose</th>
            <th class="px-6 py-3">Lote / Fabricante</th>
            <th class="px-6 py-3">Data de Aplicação</th>
            <th class="px-6 py-3">Próxima Dose</th>
            <th class="px-6 py-3">Registrado por</th>
            <th class="px-6 py-3 text-right">Ações</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr v-for="vacina in vacinacoes" :key="vacina.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 font-medium text-gray-900">{{ vacina.nome_vacina }}</td>
            <td class="px-6 py-4">
              <span class="px-2 py-1 text-xs font-semibold bg-blue-100 text-blue-800 rounded-full">{{ vacina.numero_dose }}</span>
            </td>
            <td class="px-6 py-4 text-gray-600">
              <div class="text-xs">Lote: {{ vacina.lote || 'Não informado' }}</div>
              <div class="text-xs text-gray-400">{{ vacina.fabricante || 'Não informado' }}</div>
            </td>
            <td class="px-6 py-4 text-gray-700">{{ formatarData(vacina.data_aplicacao) }}</td>
            <td class="px-6 py-4 text-gray-700 font-medium">
              <span v-if="vacina.data_proxima_dose" class="text-amber-600">{{ formatarData(vacina.data_proxima_dose) }}</span>
              <span v-else class="text-gray-400">-</span>
            </td>
            <td class="px-6 py-4 text-xs text-gray-500">{{ vacina.usuario?.name }}</td>
            <td class="px-6 py-4 text-right">
              <button @click="removerRegistro(vacina.id)" class="text-red-600 hover:text-red-900 font-semibold text-xs">Excluir</button>
            </td>
          </tr>
          <tr v-if="vacinacoes.length === 0">
            <td colspan="7" class="text-center py-8 text-gray-500">Nenhuma vacina registrada para este paciente até o momento.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="abrirModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-lg shadow-xl max-w-lg w-full p-6 relative">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Registrar Aplicação de Vacina</h3>

        <form @submit.prevent="enviarFormulario" class="space-y-4">
          <div>
            <label class="block text-xs font-medium text-gray-700">Nome da Vacina *</label>
            <input v-model="formulario.nome_vacina" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-medium text-gray-700">Dose *</label>
              <select v-model="formulario.numero_dose" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option>1ª Dose</option>
                <option>2ª Dose</option>
                <option>3ª Dose</option>
                <option>Dose Única</option>
                <option>Reforço</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-700">Número do Lote</label>
              <input v-model="formulario.lote" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
            </div>
          </div>

          <div>
            <label class="block text-xs font-medium text-gray-700">Fabricante / Laboratório</label>
            <input v-model="formulario.fabricante" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-medium text-gray-700">Data de Aplicação *</label>
              <input v-model="formulario.data_aplicacao" type="date" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-700">Próxima Dose (Aprazamento)</label>
              <input v-model="formulario.data_proxima_dose" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
            </div>
          </div>

          <div>
            <label class="block text-xs font-medium text-gray-700">Observações / Reações Adversas</label>
            <textarea v-model="formulario.observacoes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
          </div>

          <div class="flex justify-end space-x-3 pt-2">
            <button type="button" @click="abrirModal = false" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md sm:text-sm">Cancelar</button>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md sm:text-sm shadow">Salvar Registro</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
