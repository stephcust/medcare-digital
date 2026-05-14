<script setup>
import ActionMessage from '@/Components/Common/ActionMessage.vue';
import FormSection from '@/Components/Common/FormSection.vue';
import InputError from '@/Components/Common/InputError.vue';
import InputLabel from '@/Components/Common/InputLabel.vue';
import { useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Password from 'primevue/password';
import { ref } from 'vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('user-password.update'), {
        errorBag: 'updatePassword',
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value.$el.focus();
            }

            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value.$el.focus();
            }
        },
    });
};
</script>

<template>
    <FormSection @submitted="updatePassword">
        <template #title>
            Atualizar Senha
        </template>

        <template #description>
            <div class="text-justify">
                Garanta que sua conta esteja usando uma senha longa e aleatória para se manter segura.
            </div>
        </template>

        <template #form>
            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="current_password" value="Senha Atual" required />
                <Password inputId="current_password" ref="currentPasswordInput" v-model="form.current_password"
                    autocomplete="current-password" :feedback="false" fluid toggleMask />
                <InputError :message="form.errors.current_password" class="mt-2" />
            </div>

            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="password" value="Nova Senha" required />
                <Password inputId="password" ref="passwordInput" v-model="form.password" autocomplete="new-password"
                    fluid toggleMask />
                <InputError :message="form.errors.password" class="mt-2" />
            </div>

            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="password_confirmation" value="Confirmar Senha" required />
                <Password inputId="password_confirmation" v-model="form.password_confirmation"
                    autocomplete="new-password" fluid toggleMask />
                <InputError :message="form.errors.password_confirmation" class="mt-2" />
            </div>
        </template>

        <template #actions>
            <ActionMessage :on="form.recentlySuccessful" class="me-3">
                Salvo.
            </ActionMessage>

            <Button size="small" type="submit" label="Salvar" :loading="form.processing" />
        </template>
    </FormSection>
</template>
