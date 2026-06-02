<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
  <AppLayout title="Guia Médico">
    <div class="p-6 max-w-5xl mx-auto">
      <!-- <div class="mb-8"> -->
        <!-- <h1 class="text-3xl font-extrabold text-slate-900">Guia Médico</h1> -->
        <p class="text-slate-500 mt-1">O que você procura hoje?</p>


    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <Link :href="route('guia.medicos')" class="flex items-center p-6 bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition text-left group">
        <div class="p-4 rounded-full bg-blue-50 text-blue-600 mr-4 group-hover:bg-blue-100 transition">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
        </div>
        <div>
          <h3 class="text-lg font-bold text-slate-800">Consultar Médicos</h3>
          <p class="text-sm text-slate-500 mt-1">Encontre médicos credenciados por especialidade e localização.</p>
        </div>
      </Link>

      <Link :href="route('guia.clinicas')" class="flex items-center p-6 bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition text-left group">
        <div class="p-4 rounded-full bg-emerald-50 text-emerald-600 mr-4 group-hover:bg-emerald-100 transition">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
        </div>
        <div>
          <h3 class="text-lg font-bold text-slate-800">Consultar Clínicas</h3>
          <p class="text-sm text-slate-500 mt-1">Veja clínicas e laboratórios credenciados próximos a você.</p>
        </div>
      </Link>
    </div>

    </div>
  </AppLayout>
</template>
