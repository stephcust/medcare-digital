<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
</script>

<template>
    <AppLayout title="Dashboard">
        <div class="max-w-7xl mx-auto">
            <!-- Estatísticas Principais -->
            <div class="bg-surface-50 dark:bg-surface-950">
                <div class="grid grid-cols-12 gap-4">
                    <div class="col-span-12 md:col-span-6 lg:col-span-3">
                        <div class="bg-white dark:bg-surface-900 shadow p-4 rounded-border">
                            <div class="flex justify-between mb-4">
                                <div>
                                    <span
                                        class="block text-surface-500 dark:text-surface-300 font-medium mb-4">Pedidos</span>
                                    <div class="text-surface-900 dark:text-surface-0 font-medium !text-xl">152</div>
                                </div>
                                <div
                                    class="flex items-center justify-center bg-blue-100 dark:bg-blue-400/30 rounded-border w-10 h-10">
                                    <i class="pi pi-shopping-cart text-blue-500 dark:text-blue-200 !text-xl" />
                                </div>
                            </div>
                            <span class="text-green-500 font-medium">24 novos </span>
                            <span class="text-surface-500 dark:text-surface-300">na última hora</span>
                        </div>
                    </div>
                    <div class="col-span-12 md:col-span-6 lg:col-span-3">
                        <div class="bg-white dark:bg-surface-900 shadow p-4 rounded-border">
                            <div class="flex justify-between mb-4">
                                <div>
                                    <span
                                        class="block text-surface-500 dark:text-surface-300 font-medium mb-4">Receita</span>
                                    <div class="text-surface-900 dark:text-surface-0 font-medium !text-xl">R$ 2.100,00
                                    </div>
                                </div>
                                <div
                                    class="flex items-center justify-center bg-orange-100 dark:bg-orange-400/30 rounded-border w-10 h-10">
                                    <i class="pi pi-map-marker text-orange-500 dark:text-orange-200 !text-xl" />
                                </div>
                            </div>
                            <span class="text-green-500 font-medium">+52% </span>
                            <span class="text-surface-500 dark:text-surface-300">em uma semana</span>
                        </div>
                    </div>
                    <div class="col-span-12 md:col-span-6 lg:col-span-3">
                        <div class="bg-white dark:bg-surface-900 shadow p-4 rounded-border">
                            <div class="flex justify-between mb-4">
                                <div>
                                    <span
                                        class="block text-surface-500 dark:text-surface-300 font-medium mb-4">Clientes</span>
                                    <div class="text-surface-900 dark:text-surface-0 font-medium !text-xl">28.441</div>
                                </div>
                                <div
                                    class="w-10 h-10 flex items-center justify-center bg-cyan-100 dark:bg-cyan-400/30 rounded-border">
                                    <i class="pi pi-inbox text-cyan-500 dark:text-cyan-200 !text-xl" />
                                </div>
                            </div>
                            <span class="text-green-500 font-medium">520 </span>
                            <span class="text-surface-500 dark:text-surface-300">novos cadastros</span>
                        </div>
                    </div>
                    <div class="col-span-12 md:col-span-6 lg:col-span-3">
                        <div class="bg-white dark:bg-surface-900 shadow p-4 rounded-border">
                            <div class="flex justify-between mb-4">
                                <div>
                                    <span
                                        class="block text-surface-500 dark:text-surface-300 font-medium mb-4">Comentários</span>
                                    <div class="text-surface-900 dark:text-surface-0 font-medium !text-xl">152 não lidos
                                    </div>
                                </div>
                                <div
                                    class="w-10 h-10 flex items-center justify-center bg-purple-100 dark:bg-purple-400/30 rounded-border">
                                    <i class="pi pi-comment text-purple-500 dark:text-purple-200 !text-xl" />
                                </div>
                            </div>
                            <span class="text-green-500 font-medium">85 </span>
                            <span class="text-surface-500 dark:text-surface-300">respondidos</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card -->
            <div class="bg-surface-50 dark:bg-surface-950 my-3">
                <div class="bg-white dark:bg-surface-900 p-6 shadow rounded-border">
                    <div class="text-3xl font-medium text-surface-900 dark:text-surface-0 mb-2">Gráficos de Desempenho
                    </div>
                    <div class="font-medium text-surface-500 dark:text-surface-300 mb-4">Vivamus id nisl interdum,
                        blandit
                        augue sit amet, eleifend mi.</div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="h-[450px] border-2 border-dashed border-surface rounded-xl bg-surface-50"></div>
                        <div class="h-[450px] border-2 border-dashed border-surface rounded-xl bg-surface-50"></div>
                        <div
                            class="col-span-2 h-[600px] border-2 border-dashed border-surface rounded-xl bg-surface-50">
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </AppLayout>
</template>
