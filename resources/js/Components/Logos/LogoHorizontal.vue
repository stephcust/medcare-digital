<script setup>
defineProps({
    dark: Boolean
});
</script>

<template>
    <div class="flex gap-2 items-center">
        <div>
            <img class="w-10" src="/storage/medcare_logo.png" alt="MedCare Digital" />
        </div>
        <div class="flex flex-col text-center" :class="{
            'text-white': !dark,
            'text-black': dark,
        }">
        <div class="text-3xl font-[500] leading-5">MedCare</div>
        <div class="text-[13px] font-light">Digital</div>
        </div>
    </div>
</template>
