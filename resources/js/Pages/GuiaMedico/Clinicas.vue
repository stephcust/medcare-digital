<script setup>
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { router, Link } from '@inertiajs/vue3';

const propriedades = defineProps({ clinicas: Array, filtros: Object });
const busca = ref(propriedades.filtros.busca || '');

const pesquisar = () => {
  router.get(route('guia.clinicas'), { busca: busca.value }, { preserveState: true, replace: true });
};

/**
 * Função de tratamento: Limpa e normaliza os dados vindos do banco de dados.
 * Se vier como string literal do Postgres (ex: '{"A","B"}'), ela limpa os caracteres e quebra em array legítimo.
 */
const normalizarServicos = (dadosServicos) => {
  if (!dadosServicos) return [];

  // Se já for um array limpo do Javascript/JSON nativo, apenas retorna ele
  if (Array.isArray(dadosServicos)) return dadosServicos;

  // Se for a string literal legada do Postgres '{"item1","item2"}', faz o tratamento manual
  if (typeof dadosServicos === 'string') {
    return dadosServicos
      .replace(/[{}"\\]/g, '') // Remove as chaves, aspas e barras invertidas
      .split(',')              // Separa por vírgula
      .map(item => item.trim()) // Remove espaços em branco nas pontas
      .filter(item => item.length > 0);
  }

  return [];
};
</script>

<template>
    <AppLayout title="Clínicas">
  <div class="p-6 max-w-7xl mx-auto w-full">

    <div class="flex justify-start mb-4">
      <Link :href="route('guia.inicio')" class="text-sm text-slate-600 hover:text-indigo-600 font-medium flex items-center gap-1">

      </Link>
    </div>

    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-100 mb-6 flex gap-3 w-full">
      <div class="relative flex-1">
        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
        </span>
        <input
          v-model="busca"
          @input="pesquisar"
          type="text"
          placeholder="Buscar clínicas, laboratórios ou serviços..."
          class="w-full bg-slate-50 border-none rounded-lg text-sm pl-10 pr-4 py-2.5 focus:ring-2 focus:ring-indigo-500 text-left"
        />
      </div>
      <button class="px-5 py-2.5 border border-slate-200 rounded-lg text-sm font-medium hover:bg-slate-50 flex items-center gap-2 transition whitespace-nowrap">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
        Filtros
      </button>
    </div>

    <div class="text-left mb-4">
      <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ clinicas.length }} estabelecimentos encontrados</p>
    </div>

    <div class="space-y-4 w-full">
      <div
        v-for="clinica in clinicas"
        :key="clinica.id"
        class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex flex-col sm:flex-row justify-between items-start sm:items-center text-left w-full gap-4 hover:shadow-md transition duration-200"
      >
        <div class="flex items-start gap-5 flex-1 w-full">
          <div class="p-3.5 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-100 shadow-sm shrink-0 mt-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
          </div>

          <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2.5">
              <h3 class="font-extrabold text-slate-800 text-xl truncate m-0 leading-tight">{{ clinica.nome }}</h3>
              <span class="bg-blue-50 text-blue-700 border border-blue-100 px-2.5 py-0.5 text-xs font-bold rounded-md">
                {{ clinica.tipo }}
              </span>
            </div>

            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-slate-500 font-medium mt-1.5 mb-3">
              <span class="text-amber-500 font-bold flex items-center gap-0.5">★ {{ clinica.avaliacao }}</span>
              <span class="text-slate-300 hidden sm:inline">•</span>
              <span>📍 {{ clinica.distancia }}</span>
              <span class="text-slate-300 hidden sm:inline">•</span>
              <span>📞 {{ clinica.telefone }}</span>
            </div>

            <div class="flex flex-wrap gap-1.5">
              <span
                v-for="servico in normalizarServicos(clinica.servicos)"
                :key="servico"
                class="px-2.5 py-1 bg-slate-100 text-slate-600 text-xs font-semibold rounded-lg border border-slate-200 shadow-sm"
              >
                {{ servico }}
              </span>
              <span v-if="!clinica.servicos || clinica.servicos.length === 0" class="text-xs text-slate-400 italic">
                Nenhum serviço mapeado.
              </span>
            </div>
          </div>
        </div>

        <div class="flex flex-row sm:flex-col gap-2.5 w-full sm:w-auto shrink-0 pt-3 sm:pt-0 border-t border-slate-50 sm:border-none">
          <button class="flex-1 sm:flex-initial bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold px-6 py-2.5 rounded-xl shadow-sm hover:shadow transition duration-150">
            Ver Detalhes
          </button>
          <button class="flex-1 sm:flex-initial border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-bold px-6 py-2.5 rounded-xl transition duration-150 bg-white flex items-center justify-center gap-1">
            📍 Mapa
          </button>
        </div>
      </div>

      <div v-if="clinicas.length === 0" class="bg-white border border-dashed border-slate-200 rounded-2xl p-12 text-center text-slate-500 w-full">
        Nenhum estabelecimento ou laboratório credenciado encontrado.
      </div>
    </div>
  </div>
  </AppLayout>
</template>
