<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
  assinatura: Object,
  faturas: Array,
  titular_nome: String
});

const formatarDataSimples = (dataStr) => {
  if (!dataStr) return '';
  const d = new Date(dataStr);
  return d.toLocaleDateString('pt-BR');
};

const formatarDataBR = (dataStr) => {
  if (!dataStr) return '';
  const partes = dataStr.split('-');
  if (partes.length === 3) return `${partes[2]}/${partes[1]}/${partes[0]}`;
  return dataStr;
};
</script>

<template>
  <AppLayout title="Meu Plano">
    <div class="p-6 max-w-7xl mx-auto w-full">

    <div class="text-left mb-6">
      <h1 class="text-3xl font-extrabold text-slate-900">Meu Plano</h1>
      <p class="text-slate-500 mt-1">Gerencie sua carteira digital, coberturas e faturas do plano de saúde.</p>
    </div>

    <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-3xl p-6 shadow-md mb-8 text-left relative overflow-hidden">
      <span class="absolute top-6 right-6 bg-white/20 text-white text-xs font-bold px-3 py-1 rounded-full backdrop-blur-xs">
        {{ assinatura.plano.acomodacao }}
      </span>

      <div class="flex items-center gap-2 mb-8">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
        <div>
          <h2 class="text-xl font-black tracking-tight leading-none">{{ assinatura.plano.nome }}</h2>
          <span class="text-xs text-blue-200 font-medium opacity-90">{{ assinatura.plano.operadora }}</span>
        </div>
      </div>

      <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-sm">
        <div>
          <p class="text-blue-200 text-xs font-semibold uppercase tracking-wider opacity-75">Número do Cartão</p>
          <p class="font-mono font-bold text-base mt-1 tracking-wider">{{ assinatura.numero_carteirinha }}</p>
        </div>
        <div>
          <p class="text-blue-200 text-xs font-semibold uppercase tracking-wider opacity-75">Titular</p>
          <p class="font-bold text-base mt-1">{{ titular_nome }}</p>
        </div>
        <div>
          <p class="text-blue-200 text-xs font-semibold uppercase tracking-wider opacity-75">Vigência</p>
          <p class="font-bold text-base mt-1">{{ assinatura.vigencia }}</p>
        </div>
        <div>
          <p class="text-blue-200 text-xs font-semibold uppercase tracking-wider opacity-75">Início do Plano</p>
          <p class="font-bold text-base mt-1">{{ formatarDataSimples(assinatura.inicio_plano) }}</p>
        </div>
      </div>

      <div class="mt-6 pt-4 border-t border-white/10 text-xs text-blue-200 font-medium">
        Registro ANS: {{ assinatura.plano.registro_ans }}
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start w-full">

      <div class="lg:col-span-8 space-y-6 text-left">

        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
          <h3 class="text-lg font-extrabold text-slate-800">Coberturais do Plano</h3>
          <p class="text-slate-400 text-sm mb-6">Serviços incluídos e não incluídos no seu plano</p>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
            <div
              v-for="cob in assinatura.plano.coberturas"
              :key="cob.texto"
              class="flex items-start gap-3 text-sm p-2 rounded-xl transition duration-150"
              :class="!cob.incluido ? 'bg-slate-50 opacity-60' : ''"
            >
              <span
                :class="cob.incluido ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-rose-50 text-rose-500 border border-rose-100'"
                class="p-1 rounded-full shrink-0 border"
              >
                <svg v-if="cob.incluido" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                    </span>
              <div>
                <p class="font-semibold text-slate-800">{{ cob.texto }}</p>
                <span v-if="cob.detalhe" class="text-xs text-slate-400 font-medium">{{ cob.detalhe }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
          <h3 class="text-lg font-extrabold text-slate-800">Utilização em 2026</h3>
          <p class="text-slate-400 text-sm mb-6">Acompanhe o uso dos seus benefícios</p>

          <div class="space-y-5">
            <div v-for="uso in assinatura.utilizacao_atual" :key="uso.item" class="w-full">
              <div class="flex justify-between text-sm font-bold text-slate-700 mb-1.5">
                <span>{{ uso.item }}</span>
                <span class="text-slate-400 font-medium">{{ uso.usado }} de {{ uso.limite }}</span>
              </div>
              <div class="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden">
                <div
                  :style="{ width: uso.porcentagem + '%' }"
                  :class="uso.porcentagem > 80 ? 'bg-amber-500' : 'bg-blue-600'"
                  class="h-full rounded-full transition-all duration-500"
                ></div>
              </div>
            </div>
          </div>
        </div>

      </div>

      <div class="lg:col-span-4 space-y-6 text-left w-full">

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
          <h4 class="text-sm font-bold text-slate-800 mb-4">Ações Rápidas</h4>
          <div class="space-y-2.5">
            <button class="w-full py-2.5 border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-bold rounded-xl transition flex items-center justify-center gap-2 bg-white">
              📥 Baixar Carteirinha
            </button>
            <button class="w-full py-2.5 border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-bold rounded-xl transition flex items-center justify-center gap-2 bg-white">
              📄 Solicitar Reembolso
            </button>
            <button class="w-full py-2.5 border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-bold rounded-xl transition flex items-center justify-center gap-2 bg-white">
              👤 Incluir Dependente
            </button>
          </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
          <h4 class="text-sm font-bold text-slate-800">Contato</h4>
          <p class="text-xs text-slate-400 mb-4">Central de atendimento</p>

          <div class="space-y-3 text-sm font-medium">
            <div class="flex items-center gap-2.5 text-slate-700">
              <span class="text-lg">📞</span>
              <div>
                <p class="font-bold">0800 123 4567</p>
                <p class="text-xs text-slate-400">24 horas / 7 dias</p>
              </div>
            </div>
            <div class="flex items-center gap-2.5 text-slate-700 pt-1">
              <span class="text-lg">✉️</span>
              <div>
                <p class="font-bold">atendimento@medicare.com.br</p>
                <p class="text-xs text-slate-400">Resposta em até 24h</p>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
          <h4 class="text-sm font-bold text-slate-800">Faturas</h4>
          <p class="text-xs text-slate-400 mb-4">Mensalidades recentes</p>

          <div class="space-y-3">
            <div
              v-for="fat in faturas"
              :key="fat.id"
              class="flex justify-between items-center p-3 rounded-xl bg-slate-50 border border-slate-100/50"
            >
              <div>
                <p class="font-bold text-sm text-slate-800">{{ fat.mes_referencia }}</p>
                <p class="text-xs text-slate-400">Venc. {{ formatarDataBR(fat.data_vencimento) }}</p>
              </div>
              <div class="text-right">
                <p class="font-black text-sm text-slate-800">R$ {{ parseFloat(fat.valor).toFixed(2).replace('.', ',') }}</p>
                <span
                  :class="{
                    'bg-emerald-50 text-emerald-700 border border-emerald-100': fat.status === 'Pago',
                    'bg-amber-50 text-amber-700 border border-amber-100': fat.status === 'Pendente'
                  }"
                  class="px-2 py-0.5 text-[10px] font-extrabold rounded-md border uppercase inline-block mt-0.5"
                >
                  {{ fat.status }}
                </span>
              </div>
            </div>
          </div>

          <button class="w-full text-center text-sm font-bold text-blue-600 hover:text-blue-700 transition mt-4 block bg-transparent border-none cursor-pointer">
            Ver todas as faturas
          </button>

        </div>

      </div>

    </div>

  </div>
  </AppLayout>
</template>
