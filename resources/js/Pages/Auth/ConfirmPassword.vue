<script setup>
import AuthenticationCard from '@/Components/Auth/AuthenticationCard.vue';
import InputError from '@/Components/Common/InputError.vue';
import InputLabel from '@/Components/Common/InputLabel.vue';
import { Head, useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Password from 'primevue/password';
import { ref } from 'vue';

const form = useForm({
    password: '',
});

const passwordInput = ref(null);

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => {
            form.reset();

            passwordInput.value.focus();
        },
    });
};
</script>

<template>
    <Head title="Área Segura" />

    <AuthenticationCard>

        <div class="mb-4 text-sm text-gray-600">
            Esta é uma área segura do aplicativo. Por favor, confirme sua senha antes de continuar.
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="password" value="Senha" />
                <Password :feedback="false" id="password" ref="passwordInput" v-model="form.password" type="password"
                    class="mt-1 block w-full" autocomplete="current-password" autofocus />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="flex justify-end mt-4">
                <Button type="submit" label="Confirmar" class="w-full p-1 text-lg mb-4"
                    :loading="form.processing" />
            </div>
        </form>
    </AuthenticationCard>
</template>
