<script setup>
import AuthenticationCard from '@/Components/Auth/AuthenticationCard.vue';
import InputError from '@/Components/Common/InputError.vue';
import InputLabel from '@/Components/Common/InputLabel.vue';
import { Head, useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';

const props = defineProps({
    email: String,
    token: String,
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.update'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>

    <Head title="Redefinir Senha" />

    <AuthenticationCard>
        <div class="text-xl font-medium -mt-4 mb-5 text-center">Redefinir Senha</div>
        <form @submit.prevent="submit" class="min-w-[17rem] w-[17rem]">
            <div>
                <InputLabel for="email" value="E-mail" />
                <InputText id="email" v-model="form.email" type="email" class="w-full" autofocus
                    autocomplete="username" />
                <InputError :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Senha" />
                <Password toggleMask inputId="password" v-model="form.password" class="w-full"
                    autocomplete="new-password" pt:pcinput:root:class="w-full" />
                <InputError :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel for="password_confirmation" value="Confirmar Senha" />
                <Password toggleMask inputId="password_confirmation" v-model="form.password_confirmation" class="w-full"
                    autocomplete="new-password" pt:pcinput:root:class="w-full" />
                <InputError :message="form.errors.password_confirmation" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <Button icon="pi pi-key" type="submit" label="Redefinir Senha" class="w-full p-1 text-lg mb-4"
                    :loading="form.processing" />
            </div>
        </form>
    </AuthenticationCard>
</template>
