<script setup>
import ActionSection from '@/Components/Common/ActionSection.vue';
import InputError from '@/Components/Common/InputError.vue';
import InputLabel from '@/Components/Common/InputLabel.vue';
import ConfirmsPassword from '@/Components/Profile/ConfirmsPassword.vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    requiresConfirmation: Boolean,
});

const page = usePage();
const enabling = ref(false);
const confirming = ref(false);
const confirmingReq = ref(false);
const disabling = ref(false);
const regenerating = ref(false);
const showing = ref(false);
const qrCode = ref(null);
const setupKey = ref(null);
const recoveryCodes = ref([]);

const confirmationForm = useForm({
    code: '',
});

const twoFactorEnabled = computed(
    () => !enabling.value && page.props.auth.user?.two_factor_enabled,
);

watch(twoFactorEnabled, () => {
    if (!twoFactorEnabled.value) {
        confirmationForm.reset();
        confirmationForm.clearErrors();
    }
});

const enableTwoFactorAuthentication = () => {
    enabling.value = true;

    router.post(route('two-factor.enable'), {}, {
        preserveScroll: true,
        onSuccess: () => {
            enabling.value = false;
            Promise.all([
                showQrCode(),
                showSetupKey(),
                showRecoveryCodes(),
            ])
        },
        onFinish: () => {
            enabling.value = false;
            confirming.value = props.requiresConfirmation;
        },
    });
};

const showQrCode = () => {
    return axios.get(route('two-factor.qr-code')).then(response => {
        qrCode.value = response.data.svg;
    });
};

const showSetupKey = () => {
    return axios.get(route('two-factor.secret-key')).then(response => {
        setupKey.value = response.data.secretKey;
    });
}

const showRecoveryCodes = () => {
    showing.value = true;
    return axios.get(route('two-factor.recovery-codes')).then(response => {
        recoveryCodes.value = response.data;
    }).finally(() => showing.value = false);
};

const confirmTwoFactorAuthentication = () => {
    confirmingReq.value = true;
    confirmationForm.post(route('two-factor.confirm'), {
        errorBag: "confirmTwoFactorAuthentication",
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            qrCode.value = null;
            setupKey.value = null;
            confirming.value = false;
        },
        onFinish: () => confirmingReq.value = false,
    });
};

const regenerateRecoveryCodes = () => {
    regenerating.value = true;
    axios
        .post(route('two-factor.recovery-codes'))
        .then(() => showRecoveryCodes())
        .finally(() => regenerating.value = false);
};

const disableTwoFactorAuthentication = () => {
    disabling.value = true;

    router.delete(route('two-factor.disable'), {
        preserveScroll: true,
        onSuccess: () => {
            disabling.value = false;
            confirming.value = false;
        },
    });
};
</script>

<template>
    <ActionSection>
        <template #title>
            Autenticação de Dois Fatores
        </template>

        <template #description>
            <div class="text-justify">
                Adicione mais segurança à sua conta usando autenticação de dois fatores.
            </div>
        </template>

        <template #content>
            <h3 v-if="twoFactorEnabled && !confirming" class="text-lg font-medium text-gray-900 text-justify">
                Você habilitou a autenticação de dois fatores.
            </h3>

            <h3 v-else-if="twoFactorEnabled && confirming" class="text-lg font-medium text-gray-900 text-justify">
                Finalize a habilitação da autenticação de dois fatores.
            </h3>

            <h3 v-else class="text-lg font-medium text-gray-900 text-justify">
                Você não habilitou a autenticação de dois fatores.
            </h3>

            <div class="mt-3 max-w-xl text-sm text-gray-600 text-justify">
                <p>
                    Quando a autenticação de dois fatores estiver habilitada, um token será solicitado durante a
                    autenticação. Você pode obter esse token no aplicativo Google Authenticator do seu telefone.
                </p>
            </div>

            <div v-if="twoFactorEnabled" class="text-justify">
                <div v-if="qrCode">
                    <div class="mt-4 max-w-xl text-sm text-gray-600">
                        <p v-if="confirming" class="font-semibold">
                            Para finalizar a habilitação da autenticação de dois fatores, escaneie o seguinte código QR
                            usando o aplicativo autenticador do seu telefone ou insira a chave de configuração e forneça
                            o código OTP gerado.
                        </p>

                        <p v-else>
                            A autenticação de dois fatores está agora habilitada. Escaneie o seguinte código QR usando o
                            aplicativo autenticador do seu telefone ou insira a chave de configuração.
                        </p>
                    </div>

                    <div class="mt-4 p-2 inline-block bg-white" v-html="qrCode" />

                    <div v-if="setupKey" class="mt-4 max-w-xl text-sm text-gray-600">
                        <p class="font-semibold">
                            Chave de Configuração: <span v-html="setupKey"></span>
                        </p>
                    </div>

                    <div v-if="confirming" class="mt-4">
                        <InputLabel for="code" value="Código" />

                        <InputText id="code" v-model="confirmationForm.code" name="code" class="block mt-1 w-1/2"
                            inputmode="numeric" autofocus autocomplete="one-time-code"
                            @keyup.enter="confirmTwoFactorAuthentication" />

                        <InputError :message="confirmationForm.errors.code" class="mt-2" />
                    </div>
                </div>

                <div v-if="recoveryCodes.length > 0 && !confirming">
                    <div class="mt-4 max-w-xl text-sm text-gray-600">
                        <p class="font-semibold ">
                            Armazene esses códigos de recuperação em um gerenciador de senhas seguro. Eles podem ser
                            usados para recuperar o acesso à sua conta caso você perca o dispositivo de autenticação de
                            dois fatores.
                        </p>
                    </div>

                    <div class="grid gap-1 max-w-xl mt-4 px-4 py-4 font-mono text-sm bg-gray-100 rounded-lg">
                        <div v-for="code in recoveryCodes" :key="code">
                            {{ code }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-5">
                <div v-if="!twoFactorEnabled">
                    <ConfirmsPassword @confirmed="enableTwoFactorAuthentication">
                        <Button label="Ativar" class="mr-2" size="small" type="button" :loading="enabling" />
                    </ConfirmsPassword>
                </div>

                <div v-else>
                    <ConfirmsPassword @confirmed="confirmTwoFactorAuthentication">
                        <Button label="Confirmar" class="mr-2" size="small" type="button"
                            :loading="disabling || confirmingReq" v-if="confirming" />
                    </ConfirmsPassword>

                    <ConfirmsPassword @confirmed="regenerateRecoveryCodes">
                        <Button label="Regenerar Códigos de Recuperação" class="mr-2" size="small"
                            v-if="recoveryCodes.length > 0 && !confirming" :loading="regenerating || disabling" />
                    </ConfirmsPassword>

                    <ConfirmsPassword @confirmed="showRecoveryCodes">
                        <Button label="Ver Códigos de Recuperação" class="mr-2" size="small" severity="info"
                            v-if="recoveryCodes.length === 0 && !confirming" :loading="showing || disabling" />
                    </ConfirmsPassword>

                    <ConfirmsPassword @confirmed="disableTwoFactorAuthentication">
                        <Button label="Cancelar" class="mr-2" size="small"
                            :loading="disabling || enabling || confirmingReq" outlined v-if="confirming" />
                    </ConfirmsPassword>

                    <ConfirmsPassword @confirmed="disableTwoFactorAuthentication">
                        <Button label="Desabilitar" class="mr-2" size="small" severity="danger"
                            :loading="disabling || regenerating || showing" v-if="!confirming" />
                    </ConfirmsPassword>
                </div>
            </div>
        </template>
    </ActionSection>
</template>
