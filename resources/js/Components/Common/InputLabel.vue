<script setup>
import RequiredMark from './RequiredMark.vue';

defineProps({
    value: String,
    required: {
        type: Boolean,
        default: false,
        required: false,
    }
});
</script>

<template>
    <label class="block font-medium text-sm text-gray-700">
        <span v-if="value">{{ value }}<RequiredMark class="text-xl font-medium leading-3" v-if="required" /> </span>
        <span v-else><slot /></span>
    </label>
</template>
