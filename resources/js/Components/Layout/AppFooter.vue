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

                <!-- Informações de Desenvolvimento -->
                <div v-if="!app.isProduction" class="text-orange-500 text-center">
                    Ambiente de Desenvolvimento - Laravel v{{ app.laravelVersion }} | PHP v{{ app.phpVersion }} | Vue.js v{{ app.vueVersion }}
                </div>
            </div>
        </div>
    </footer>
</template>
