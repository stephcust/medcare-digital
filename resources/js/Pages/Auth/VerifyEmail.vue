<script setup>
import AuthenticationCard from '@/Components/Auth/AuthenticationCard.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import { computed } from 'vue';

const props = defineProps({
    status: String,
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');
</script>

<template>
    <Head title="Verifique seu E-mail" />

    <AuthenticationCard>
            <div class="text-xl font-medium -mt-8 mb-3 text-center">Verifique seu E-mail</div>
        <div class="w-96">
            <div class="mb-4 text-sm text-gray-600 text-justify">
                Antes de continuar, você poderia verificar seu endereço de e-mail clicando no link que acabamos de enviar para você por e-mail? Se você não recebeu o e-mail, teremos prazer em enviar outro.
            </div>

            <div v-if="verificationLinkSent" class="mb-4 font-medium text-sm text-green-600">
                Um novo link de verificação foi enviado para o endereço de e-mail fornecido nas configurações do seu perfil.
            </div>

            <form @submit.prevent="submit">
                <div class="mt-4 flex flex-col items-center justify-between">
                    <Button type="submit" label="Reenviar E-mail de Verificação" class="p-1 text-lg w-full mb-4"
                        :loading="form.processing" />

                    <div class="flex gap-2">
                        <Link
                            :href="route('profile.show')"
                            class="font-medium no-underline ml-2 cursor-pointer hover:text-primary-700 transition-all"
                        >
                            Editar Perfil</Link>

                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="font-medium no-underline ml-2 cursor-pointer hover:text-primary-700 transition-all"
                        >
                            Sair
                        </Link>
                    </div>
                </div>
            </form>
        </div>
    </AuthenticationCard>
</template>
