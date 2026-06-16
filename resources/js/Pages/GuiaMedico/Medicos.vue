<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';

const propriedades = defineProps({ medicos: Array, filtros: Object });
const busca = ref(propriedades.filtros.busca || '');

const pesquisar = () => {
  router.get(route('guia.medicos'), { busca: busca.value }, { preserveState: true, replace: true });
};

const obterIniciais = (nome) => nome.split(' ').slice(1, 3).map(n => n[0]).join('').toUpperCase();
</script>

<template>
  <AppLayout title="Médicos">
    <div class="p-6 max-w-7xl mx-auto w-full">

    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-100 mb-6 flex gap-3">
      <input v-model="busca" @input="pesquisar" type="text" placeholder="Buscar por nome, especialidade ou local..." class="w-full bg-slate-50 border-none rounded-lg text-sm focus:ring-2 focus:ring-indigo-500" />
      <button class="px-4 py-2 border border-slate-200 rounded-lg text-sm font-medium hover:bg-slate-50 flex items-center gap-1">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
        Filtros
      </button>
    </div>

    <p class="text-xs font-semibold text-slate-400 mb-4 uppercase tracking-wider">{{ medicos.length }} médicos encontrados</p>

    <div class="space-y-4">
      <div v-for="medico in medicos" :key="medico.id" class="bg-white p-5 rounded-xl border border-slate-100 shadow-sm flex justify-between items-center text-left">
        <div class="flex items-center gap-4">
          <div class="h-12 w-12 rounded-full bg-blue-50 text-blue-600 font-bold flex items-center justify-center text-sm">
            {{ obterIniciais(medico.nome) }}
          </div>
          <div>
            <div class="flex items-center gap-2">
              <h3 class="font-bold text-slate-800 text-lg">{{ medico.nome }}</h3>
              <span :class="{
                'bg-emerald-50 text-emerald-700': medico.status === 'Disponível',
                'bg-rose-50 text-rose-700': medico.status === 'Emergência'
              }" class="px-2 py-0.5 text-xs font-semibold rounded-md">
                {{ medico.status }}
              </span>
            </div>
            <p class="text-sm font-semibold text-blue-600 mt-0.5">{{ medico.especialidade }}</p>
            <div class="flex items-center gap-3 text-xs text-slate-400 mt-2">
              <span class="flex items-center gap-1 text-amber-500 font-bold">★ {{ medico.avaliacao }}</span>
              <span>•</span>
              <span>📍 {{ medico.distancia }}</span>
              <span>•</span>
              <span>📞 {{ medico.telefone }}</span>
            </div>
          </div>
        </div>
        <div class="flex flex-col gap-2">
          <button class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-5 py-2 rounded-lg shadow-sm transition">Agendar</button>
          <button class="border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-semibold px-5 py-2 rounded-lg transition">Ver Perfil</button>
        </div>
      </div>
    </div>
    </div>
  </AppLayout>
</template>
