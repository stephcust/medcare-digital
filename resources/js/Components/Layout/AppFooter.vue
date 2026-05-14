<script setup>
import { useApp } from '@/Assets/Composables';

const app = useApp();
</script>

<template>
    <footer>
        <div class="grid place-content-center pt-2 pb-1">
            <div class="max-w-7xl text-sm" style="line-height: 1rem;">
                <!-- Aplicação -->
                <div class="text-gray-500 font-semibold text-center">
                    {{ app.appName }} <span v-if="app.appVersion">- v{{ app.appVersion }}</span>
                </div>
                <!-- Departamento -->
                <div class="text-gray-400 text-center">
                    <a href="https://semsa.manaus.am.gov.br/"
                        class="hover:text-primary-400 transition-all duration-300">
                        Departamento de Tecnologia da Informação - SEMSA
                    </a>
                    -
                    <a href="https://www.manaus.am.gov.br/" class="hover:text-primary-400 transition-all duration-300">
                        Prefeitura de Manaus
                    </a>
                </div>
                <!-- Informações de Desenvolvimento -->
                <div v-if="!app.isProduction" class="text-orange-500 text-center">
                    Ambiente de Desenvolvimento - Laravel v{{ app.laravelVersion }} | PHP v{{ app.phpVersion }} | Vue.js v{{ app.vueVersion }}
                </div>
            </div>
        </div>
    </footer>
</template>
