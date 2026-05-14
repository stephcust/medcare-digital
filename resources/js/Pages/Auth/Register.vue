<script setup>
import AuthenticationCard from '@/Components/Auth/AuthenticationCard.vue';
import InputError from '@/Components/Common/InputError.vue';
import InputLabel from '@/Components/Common/InputLabel.vue';
import RequiredMark from '@/Components/Common/RequiredMark.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

</script>

<template>

    <Head title="Cadastro" />

    <AuthenticationCard>
        <div class="text-xl font-medium -mt-4 mb-5 text-center">Cadastro</div>


        <form @submit.prevent="submit" class="min-w-[17rem]">
            <div>
                <InputLabel for="name" value="Nome" required />
                <InputText id="name" v-model="form.name" class="mt-1 block w-full" autocomplete="name" required />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="mt-4">
                <InputLabel for="email" value="E-mail" required />
                <InputText id="email" v-model="form.email" class="mt-1 block w-full" required autocomplete="username" />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Senha" required />
                <Password id="password" v-model="form.password" class="mt-1 block w-full" required
                    autocomplete="new-password" pt:pcinput:root:class="w-full" toggleMask />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel for="password_confirmation" value="Confirmar Senha" required />
                <Password id="password_confirmation" v-model="form.password_confirmation" class="mt-1 block w-full"
                    required autocomplete="new-password" pt:pcinput:root:class="w-full" toggleMask />
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <div v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature" class="mt-4 flex items-center">
                <Checkbox binary inputId="terms" v-model="form.terms" name="terms" required />

                <label for="terms" class="ms-2 max-w-[35ch] text-sm">
                    Eu concordo com os <a target="_blank" :href="route('terms.show')"
                        class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Termos
                        de Serviço</a> e a <a target="_blank" :href="route('policy.show')"
                        class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Política
                        de Privacidade</a>
                    <RequiredMark />
                </label>
                <InputError class="mt-2" :message="form.errors.terms" />
            </div>

            <div class="flex flex-col items-center justify-end mt-4">

                <Button type="submit" label="Cadastrar" class="w-full p-1 text-xl mb-4"
                    :loading="form.processing" />
                <Link :href="route('login')"
                    class="font-medium no-underline ml-2 cursor-pointer hover:text-primary-700 transition-all">
                Possui cadastro?
                </Link>
            </div>
        </form>
    </AuthenticationCard>
</template>
