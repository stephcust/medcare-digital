<script setup>
import AuthenticationCard from '@/Components/Auth/AuthenticationCard.vue';
import InputError from '@/Components/Common/InputError.vue';
import InputLabel from '@/Components/Common/InputLabel.vue';
import { Head, useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';

defineProps({
    status: String,
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <Head title="Recuperar Senha" />

    <AuthenticationCard>
        <div class="w-96">
            <div class="text-xl font-medium -mt-4 mb-5 text-center">Recuperação de Senha</div>

            <div class="mb-4 text-sm text-gray-600">
                Esqueceu sua senha? Sem problemas. Apenas nos informe seu endereço de e-mail e enviaremos um link de redefinição por e-mail que permitirá que você escolha uma nova senha.
            </div>

            <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
                {{ status }}
            </div>

            <form @submit.prevent="submit">
                <div>
                    <InputLabel for="email" value="E-mail" required />
                    <InputText id="email" v-model="form.email" type="email" class="mt-1 block w-full" autofocus
                        autocomplete="username" />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div class="flex items-center justify-end mt-4">
                    <Button icon="pi pi-send" type="submit" label="Enviar Link de Redefinição"
                        class="w-full p-1 text-lg mb-4" :loading="form.processing" />
                </div>
            </form>
        </div>
    </AuthenticationCard>
</template>
