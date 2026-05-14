<script setup>
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Password from 'primevue/password';
import { nextTick, reactive, ref } from 'vue';
import InputError from '@/Components/Common/InputError.vue';

const emit = defineEmits(['confirmed']);

defineProps({
    title: {
        type: String,
        default: 'Confirmar Senha',
    },
    content: {
        type: String,
        default: 'Para sua segurança, por favor confirme sua senha para continuar.',
    },
    button: {
        type: String,
        default: 'Confirmar',
    },
});

const confirmingPassword = ref(false);
const passwordInput = ref(null);

const form = reactive({
    password: '',
    error: '',
    processing: false,
});

const startConfirmingPassword = () => {
    axios.get(route('password.confirmation')).then(response => {
        if (response.data.confirmed) {
            emit('confirmed');
        } else {
            confirmingPassword.value = true;
            setTimeout(() => {
                const inputEl = passwordInput.value.$el.querySelector('input');
                inputEl.focus();
            }, 500);
        }
    });
};

const confirmPassword = () => {
    form.processing = true;

    axios.post(route('password.confirm'), {
        password: form.password,
    }).then(() => {
        form.processing = false;

        closeModal();
        nextTick().then(() => emit('confirmed'));

    }).catch(error => {
        form.processing = false;
        form.error = error.response.data.errors.password[0];
        passwordInput.value.focus();
    });
};

const closeModal = () => {
    confirmingPassword.value = false;
    form.password = '';
    form.error = '';
};
</script>

<template>
    <span>
        <span @click="startConfirmingPassword">
            <slot />
        </span>

        <Dialog class="w-96" position="top" v-model:visible="confirmingPassword" @hide="closeModal" modal
            :header="title">
            <template #default>
                <div class="text-justify">
                    {{ content }}
                </div>

                <div class="mt-4">
                    <Password ref="passwordInput" v-model="form.password" placeholder="Senha"
                        autocomplete="current-password" @keyup.enter="confirmPassword" toggleMask fluid
                        :feedback="false" />

                    <InputError :message="form.error" class="mt-2" />
                </div>
            </template>

            <template #footer>
                <div class="flex gap-2">
                    <Button class="bg-tertiary-500 border-tertiary-500" size="small" label="Cancelar"
                        @click="closeModal" />

                    <Button :label="button" type="submit" size="small" :loading="form.processing"
                        @click="confirmPassword" />
                </div>
            </template>
        </Dialog>
    </span>
</template>
