<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
  historico: Array,
  estatisticas: Object
});

// Auxiliar para formatação amigável de data e hora no frontend
const formatarData = (dataStr) => {
  if (!dataStr) return '';
  const d = new Date(dataStr);
  return d.toLocaleDateString('pt-BR') + ' às ' + d.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
};
</script>


<template>
    <AppLayout title="Histórico Clínico">
  <div class="p-6 max-w-7xl mx-auto w-full">

    <div class="text-left mb-6">
      <h1 class="text-3xl font-extrabold text-slate-900">Histórico de Pronto Socorro</h1>
      <p class="text-slate-500 mt-1">Linha do tempo de atendimentos médicos emergenciais e urgências.</p>
    </div>

    <div v-if="estatisticas.total > 0" class="bg-blue-50 border border-blue-100 rounded-2xl p-4 flex items-center gap-3 text-left text-sm text-blue-900 mb-8 shadow-sm">
      <div class="p-2 bg-blue-600 text-white rounded-full shrink-0">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
      </div>
      <div>
        <span class="font-extrabold">Total de atendimentos: {{ estatisticas.total }}</span>
        <span class="text-slate-300 mx-2">•</span>
        <span>Último atendimento em <strong class="font-bold">{{ estatisticas.ultimo_data }}</strong> — {{ estatisticas.ultimo_local }}</span>
      </div>
    </div>

    <div class="relative border-l-2 border-slate-200 ml-4 md:ml-6 space-y-8 text-left w-full">

      <div v-for="registro in historico" :key="registro.id" class="relative pl-8 w-full group">

        <span
          :class="{
            'bg-rose-500 ring-rose-100 text-white': registro.gravidade === 'Alta Gravidade',
            'bg-amber-500 ring-amber-100 text-white': registro.gravidade === 'Média Gravidade'
          }"
          class="absolute -left-[13px] top-1.5 rounded-full h-6 w-6 flex items-center justify-center ring-4 shadow-sm transition"
        >
          <svg v-if="registro.gravidade === 'Alta Gravidade'" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zm-1 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" /></svg>
        </span>

        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm w-full hover:shadow-md transition duration-200">

          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-slate-50 pb-4 mb-4">
            <div>
              <div class="flex flex-wrap items-center gap-3">
                <h2 class="text-xl font-extrabold text-slate-800 m-0">{{ registro.motivo_atendimento }}</h2>
                <span
                  :class="{
                    'bg-rose-50 text-rose-700 border border-rose-100': registro.gravidade === 'Alta Gravidade',
                    'bg-amber-50 text-amber-700 border border-amber-100': registro.gravidade === 'Média Gravidade'
                  }"
                  class="px-2.5 py-0.5 text-xs font-bold rounded-full"
                >
                  {{ registro.gravidade }}
                </span>
              </div>

              <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-slate-400 font-medium mt-2">
                <span class="flex items-center gap-1">🕒 {{ formatarData(registro.data_atendimento) }}</span>
                <span class="hidden sm:inline">•</span>
                <span class="flex items-center gap-1">📍 {{ registro.local_atendimento }}</span>
              </div>
            </div>

            <button class="px-4 py-2 border border-slate-200 rounded-xl text-sm font-bold text-slate-700 hover:bg-slate-50 transition flex items-center gap-1.5 shadow-sm shrink-0 bg-white">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
              Relatório
            </button>
          </div>

          <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <div class="lg:col-span-7 space-y-4">
              <div>
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1 flex items-center gap-1">👤 Médico</h4>
                <p class="text-slate-700 font-medium text-sm">{{ registro.medico_nome }}</p>
              </div>

              <div>
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1 flex items-center gap-1">📄 Diagnóstico</h4>
                <p class="text-slate-700 font-semibold text-base bg-slate-50 p-3 rounded-xl border border-slate-100">{{ registro.diagnostico }}</p>
              </div>

              <div>
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1 flex items-center gap-1">🛠️ Tratamento Administrado</h4>
                <p class="text-slate-600 text-sm leading-relaxed">{{ registro.tratamento }}</p>
              </div>
            </div>

            <div class="lg:col-span-5 space-y-4 lg:border-l lg:border-slate-50 lg:pl-6">
              <div>
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 flex items-center gap-1">🧪 Exames Realizados</h4>
                <div class="flex flex-wrap gap-1.5">
                  <span v-for="exame in registro.exames_realizados" :key="exame" class="px-2.5 py-1 bg-blue-50 text-blue-700 border border-blue-100 text-xs font-bold rounded-lg shadow-xs flex items-center gap-1">
                    <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span> {{ exame }}
                  </span>
                </div>
                <p v-if="!registro.exames_realizados.length" class="text-xs text-slate-400 italic">Nenhum exame solicitado.</p>
              </div>

              <div>
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 flex items-center gap-1">💊 Medicamentos Aplicados</h4>
                <div class="flex flex-wrap gap-1.5">
                  <span v-for="med in registro.medicamentos" :key="med.nome" class="px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-100 text-xs font-bold rounded-lg shadow-xs flex items-center gap-1">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> {{ med.nome }} <span class="font-normal text-emerald-600 opacity-80">({{ med.dosagem }})</span>
                  </span>
                </div>
                <p v-if="!registro.medicamentos.length" class="text-xs text-slate-400 italic">Nenhuma medicação ministrada.</p>
              </div>
            </div>
          </div>

          <div class="mt-5 pt-4 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center gap-x-6 gap-y-2 text-sm">
            <p class="text-slate-600"><strong class="font-bold text-slate-800">Desfecho:</strong> {{ registro.desfecho }}</p>
            <p v-if="registro.acompanhamento" class="text-slate-600"><strong class="font-bold text-slate-800">Acompanhamento recomendado:</strong> {{ registro.acompanhamento }}</p>
          </div>

        </div>
      </div>
    </div>

    <div v-if="historico.length === 0" class="bg-white border border-dashed border-slate-200 rounded-3xl p-16 text-center text-slate-400 max-w-xl mx-auto mt-12">
      <p class="text-lg font-bold">Nenhum registro encontrado</p>
      <p class="text-sm mt-1">Este paciente não possui passagens registradas pelo Pronto Socorro.</p>
    </div>
  </div>
    </AppLayout>
</template>
