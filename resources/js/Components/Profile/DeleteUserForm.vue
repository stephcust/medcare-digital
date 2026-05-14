<script setup>
import ActionSection from '@/Components/Common/ActionSection.vue';
import InputError from '@/Components/Common/InputError.vue';
import { useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Password from 'primevue/password';
import { ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);
const deleting = ref(false);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    setTimeout(() => passwordInput.value.$el.focus(), 250);
};

const deleteUser = () => {
    deleting.value = true;
    form.delete(route('current-user.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.$el.focus(),
        onFinish: () => {
            form.reset();
            deleting.value = false
        },
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.reset();
};
</script>

<template>
    <ActionSection>
        <template #title>
            Excluir Conta
        </template>

        <template #description>
            <div class="text-justify">
                Excluir permanentemente sua conta.
            </div>
        </template>

        <template #content>
            <div class="max-w-xl text-sm text-gray-600">
                Uma vez que sua conta for excluída, todos os seus recursos e dados serão excluídos permanentemente.
                Antes de excluir sua conta, faça o download de quaisquer dados ou informações que você deseja manter.
            </div>

            <div class="mt-5">
                <Button size="small" :loading="deleting" severity="danger" type="button" @click="confirmUserDeletion">
                    Excluir Conta
                </Button>
            </div>

        </template>
    </ActionSection>

    <!-- Delete Account Confirmation Modal -->
    <Dialog position="top" class="max-w-lg" header="Excluir Conta" modal v-model:visible="confirmingUserDeletion"
        @hide="closeModal">
        <template #default>
            <div class="text-justify">
                Tem certeza de que deseja excluir sua conta? Uma vez que sua conta for excluída, todos os seus
                recursos e dados serão excluídos permanentemente. Por favor, digite sua senha para confirmar que
                você deseja excluir permanentemente sua conta.
            </div>

            <div class="mt-4">
                <Password ref="passwordInput" v-model="form.password" placeholder="Senha"
                    autocomplete="current-password" @keyup.enter="deleteUser" :feedback="false" fluid toggleMask />

                <InputError :message="form.errors.password" class="mt-2" />
            </div>
        </template>

        <template #footer>
            <Button label="Cancelar" type="button" size="small" class="bg-tertiary-500 border-tertiary-500"
                @click="closeModal" :disabled="form.processing" />

            <Button size="small" type="submit" label="Excluir Conta" severity="danger" :loading="form.processing"
                @click="deleteUser" />
        </template>
    </Dialog>
</template>
