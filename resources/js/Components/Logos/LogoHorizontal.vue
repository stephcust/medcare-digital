<script setup>
defineProps({
    dark: Boolean
});
</script>

<template>
    <div class="flex gap-2 items-center">
        <div class="bg-white rounded-md p-1 shadow-sm">
            <img class="w-10" src="/storage/logo_medcare2.png" alt="MedCare Digital" />
        </div>
        <div class="hidden flex-col text-center drop-shadow-sm sm:flex" :class="{
            'text-white': !dark,
            'text-black': dark,
        }">
        <div class="text-3xl font-[500] leading-5">MedCare</div>
        <div class="text-[13px] font-light">Digital</div>
        </div>
    </div>
</template>
