<script setup>
import AuthenticationCard from '@/Components/Auth/AuthenticationCard.vue';
import InputError from '@/Components/Common/InputError.vue';
import InputLabel from '@/Components/Common/InputLabel.vue';
import { Head, useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import { nextTick, ref } from 'vue';

const recovery = ref(false);

const form = useForm({
    code: '',
    recovery_code: '',
});

const recoveryCodeInput = ref(null);
const codeInput = ref(null);

const toggleRecovery = async () => {
    recovery.value ^= true;

    await nextTick();

    if (recovery.value) {
        recoveryCodeInput.value.$el.focus();
        form.code = '';
    } else {
        codeInput.value.$el.focus();
        form.recovery_code = '';
    }
};

const submit = () => {
    form.post(route('two-factor.login'));
};
</script>

<template>

    <Head title="Autenticação em Dois Fatores" />

    <AuthenticationCard>
        <div class="w-96">
            <div class="text-xl font-medium -mt-8 mb-3 text-center">Autenticação em 2 Fatores</div>
            <div class="mb-4 text-sm text-gray-600 text-justify h-[8ch]">
                <template v-if="!recovery">
                    Por favor, confirme o acesso à sua conta inserindo o código de autenticação fornecido pelo seu
                    aplicativo de autenticação.
                </template>

                <template v-else>
                    Por favor, confirme o acesso à sua conta inserindo um dos seus códigos de recuperação de emergência.
                </template>
            </div>

            <form @submit.prevent="submit">
                <div v-if="!recovery">
                    <InputLabel for="code" value="Código" />
                    <InputText id="code" ref="codeInput" v-model="form.code" class="mt-1 block w-full" autofocus
                        autocomplete="one-time-code" />
                    <InputError class="mt-2" :message="form.errors.code" />
                </div>

                <div v-else>
                    <InputLabel for="recovery_code" value="Código de Recuperação" />
                    <InputText id="recovery_code" ref="recoveryCodeInput" v-model="form.recovery_code" type="text"
                        class="mt-1 block w-full" autocomplete="one-time-code" />
                    <InputError class="mt-2" :message="form.errors.recovery_code" />
                </div>

                <div class="flex flex-col items-center justify-end mt-4 gap-3">

                    <Button text type="button"
                        class="text-sm text-gray-600 hover:text-gray-900 underline cursor-pointer disabled:"
                        @click.prevent="toggleRecovery">
                        <template v-if="!recovery">
                            <span class="!no-underline">
                                <i class="pi pi-refresh"></i>
                            </span>
                            Usar um código de recuperação
                        </template>

                        <template v-else>
                            <span class="!no-underline">
                                <i class="pi pi-key"></i>
                            </span>
                            Usar o código de autenticação
                        </template>
                    </Button>

                    <Button type="submit" label="Confirmar" icon="pi pi-check" class="w-full p-1 text-lg text-white"
                        :loading="form.processing" />
                </div>
            </form>
        </div>
    </AuthenticationCard>
</template>
