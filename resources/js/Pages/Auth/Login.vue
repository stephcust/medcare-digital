<script setup>
import AuthenticationCard from '@/Components/Auth/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/Auth/AuthenticationCardLogo.vue';
import InputError from '@/Components/Common/InputError.vue';
import InputLabel from '@/Components/Common/InputLabel.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    cpf: '',
    password: '',
    remember: false,
});
const formatCPF = (value) => {
    return value
        .replace(/\D/g, '') // Remove tudo que não for número
        .replace(/(\d{3})(\d)/, '$1.$2') // Adiciona o primeiro ponto
        .replace(/(\d{3})(\d)/, '$1.$2') // Adiciona o segundo ponto
        .replace(/(\d{3})(\d{1,2})$/, '$1-$2'); // Adiciona o traço
};

const submit = () => {
    form.transform(data => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>

    <Head title="Entrar" />

    <AuthenticationCard>
        <AuthenticationCardLogo />
        <div class="text-500 font-medium my-5 text-center">Faça login para continuar</div>

        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="flex flex-col gap-4 w-72 sm:w-96">
            <InputLabel for="cpf" value="CPF" required />
            <InputText class="w-full" id="cpf" type="text" v-model="form.cpf"
                placeholder="Digite seu CPF" autocomplete="username" @input="form.cpf = formatCPF($event.target.value)" />
            <InputError class="mt-2 text-wrap text-justify" :message="form.errors.cpf" />

            <div>
                <InputLabel for="password" value="Senha" required />
                <Password fluid toggleMask placeholder="Digite sua senha" inputId="password" v-model="form.password"
                    :feedback="false" />
                <InputError class="mt-2 text-wrap text-justify" :message="form.errors.password" />
            </div>

            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center">
                    <Checkbox inputId="remember" v-model="form.remember" binary name="remember" class="mr-2" />
                    <label for="remember">Lembrar credenciais</label>
                </div>
                <Link v-if="canResetPassword" :href="route('password.request')"
                    class="font-medium no-underline text-right cursor-pointer hover:text-primary-700 transition-all">
                Esqueceu a senha?</Link>
            </div>
            <Button type="submit" label="Entrar" class="w-full p-1 text-lg mb-4" :loading="form.processing" />
            <div v-if="route().has('register')" class="text-center w-full font-normal">
                Não tem conta?
                <Link :href="route('register')"
                    class="font-medium no-underline ml-2 cursor-pointer hover:text-primary-700 transition-all">
                Cadastre-se aqui
                </Link>
            </div>
        </form>
    </AuthenticationCard>
</template>
