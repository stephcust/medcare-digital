<script setup>
import ActionMessage from '@/Components/Common/ActionMessage.vue';
import ActionSection from '@/Components/Common/ActionSection.vue';
import FormSection from '@/Components/Common/FormSection.vue';
import InputError from '@/Components/Common/InputError.vue';
import InputLabel from '@/Components/Common/InputLabel.vue';
import SectionBorder from '@/Components/Common/SectionBorder.vue';
import { useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import { ref } from 'vue';

const props = defineProps({
    tokens: Array,
    availablePermissions: Array,
    defaultPermissions: Array,
});

const createApiTokenForm = useForm({
    name: '',
    permissions: props.defaultPermissions,
});

const updateApiTokenForm = useForm({
    permissions: [],
});

const deleteApiTokenForm = useForm({});

const displayingToken = ref(false);
const managingPermissionsVisible = ref(false);
const deletingPermissionsVisible = ref(false);
const managingPermissionsFor = ref(null);
const apiTokenBeingDeleted = ref(null);

const createApiToken = () => {
    createApiTokenForm.post(route('api-tokens.store'), {
        preserveScroll: true,
        onSuccess: () => {
            displayingToken.value = true;
            createApiTokenForm.reset();
        },
    });
};

const manageApiTokenPermissions = (token) => {
    updateApiTokenForm.permissions = token.abilities;
    managingPermissionsFor.value = token;
    managingPermissionsVisible.value = true;
};

const updateApiToken = () => {
    updateApiTokenForm.put(route('api-tokens.update', managingPermissionsFor.value), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            managingPermissionsFor.value = null
            managingPermissionsVisible.value = false
        },
    });
};

const confirmApiTokenDeletion = (token) => {
    deletingPermissionsVisible.value = true;
    apiTokenBeingDeleted.value = token;
};

const deleteApiToken = () => {
    deleteApiTokenForm.delete(route('api-tokens.destroy', apiTokenBeingDeleted.value), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            apiTokenBeingDeleted.value = null
            deletingPermissionsVisible.value = false
        },
    });
};

function getPermissionPTBR(permission) {
    switch (permission) {
        case 'create': return 'Criar';
        case 'read': return 'Ler';
        case 'update': return 'Atualizar';
        case 'delete': return 'Deletar';
        default: return permission;
    }
}
</script>

<template>
    <div>
        <!-- Gerar Token de API -->
        <FormSection @submitted="createApiToken">
            <template #title>
                Criar um Token de API
            </template>

            <template #description>
                Tokens de API permitem que serviços de terceiros se autentiquem em nosso aplicativo em seu nome.
            </template>

            <template #form>
                <!-- Token Name -->
                <div class="col-span-6 sm:col-span-4">
                    <InputLabel for="name" value="Nome" required />
                    <InputText id="name" v-model="createApiTokenForm.name" fluid autofocus />
                    <InputError :message="createApiTokenForm.errors.name" class="mt-2" />
                </div>

                <!-- Token Permissions -->
                <div v-if="availablePermissions.length > 0" class="col-span-6">
                    <InputLabel for="permissions" value="Permissões" />

                    <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div v-for="permission in availablePermissions" :key="permission">
                            <label class="flex items-center">
                                <Checkbox v-model="createApiTokenForm.permissions" :value="permission" />
                                <span class="ms-2 text-sm text-gray-600">{{ getPermissionPTBR(permission) }} ({{
                                    permission }})</span>
                            </label>
                        </div>
                    </div>
                </div>
            </template>

            <template #actions>
                <ActionMessage :on="createApiTokenForm.recentlySuccessful" class="me-3">
                    Criado.
                </ActionMessage>

                <Button type="submit" size="small" label="Criar" :loading="createApiTokenForm.processing" />
            </template>
        </FormSection>

        <div v-if="tokens.length > 0">
            <SectionBorder />

            <!-- Manage Tokens de API -->
            <div class="mt-10 sm:mt-0">
                <ActionSection>
                    <template #title>
                        Gerenciar Tokens de API
                    </template>

                    <template #description>
                        Você pode deletar qualquer um dos seus tokens existentes se eles não forem mais necessários.
                    </template>

                    <!-- Token de API List -->
                    <template #content>
                        <div class="flex items-center justify-between mb-2 text-lg font-semibold">
                            <div>
                                Nome do Token
                            </div>
                            <div class="mr-5">Ações</div>
                        </div>
                        <div class="space-y-6">
                            <div v-for="token in tokens" :key="token.id" class="flex items-center justify-between">
                                <div class="break-all">
                                    {{ token.name }}
                                </div>

                                <div class="flex items-center ms-2 gap-2">
                                    <div v-if="token.last_used_ago" class="text-sm text-gray-400">
                                        Last used {{ token.last_used_ago }}
                                    </div>

                                    <Button size="small" outlined class="p-1 px-3"
                                        v-if="availablePermissions.length > 0" severity="warn"
                                        label="Alterar Permissões" @click="manageApiTokenPermissions(token)" />

                                    <Button size="small" class="p-1 px-3" severity="danger" label="Apagar"
                                        @click="confirmApiTokenDeletion(token)" />
                                </div>
                            </div>
                        </div>
                    </template>
                </ActionSection>
            </div>
        </div>

        <!-- Token Value Modal -->
        <Dialog class="max-w-lg" position="top" header="Token de API Gerado" modal v-model:visible="displayingToken"
            @hide="displayingToken = false">
            <template #default>
                <div>
                    Por favor, copie seu novo Token de API. Para sua segurança, ele não será mostrado novamente.
                </div>

                <div v-if="$page.props.jetstream.flash.token"
                    class="mt-4 bg-gray-100 px-4 py-2 rounded font-mono text-sm text-gray-500 break-all">
                    {{ $page.props.jetstream.flash.token }}
                </div>
            </template>

            <template #footer>
                <Button class="bg-tertiary-500 border-tertiary-500" label="Fechar" size="small"
                    @click="displayingToken = false" />
            </template>
        </Dialog modal>

        <!-- Token de API Permissions Modal -->
        <Dialog class="max-w-xl w-full" position="top" modal header="Alterar Permissões do Token de API"
            v-model:visible="managingPermissionsVisible" @hide="managingPermissionsFor = null">
            <template #default>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div v-for="permission in availablePermissions" :key="permission">
                        <label class="flex items-center">
                            <Checkbox v-model="updateApiTokenForm.permissions" :value="permission" />
                            <span class="ms-2 text-sm text-gray-600">{{ getPermissionPTBR(permission) }} ({{
                                permission }})</span>
                        </label>
                    </div>
                </div>
            </template>

            <template #footer>
                <Button class="bg-tertiary-500 border-tertiary-500" :disabled="updateApiTokenForm.processing"
                    size="small" label="Cancelar" @click="managingPermissionsVisible = false" />

                <Button severity="warn" size="small" icon="pi pi-pencil" label="Alterar" type="submit"
                    :loading="updateApiTokenForm.processing" @click="updateApiToken" />
            </template>
        </Dialog modal>

        <!-- Delete Token Confirmation Modal -->
        <Dialog header="Apagar Token de API" class="max-w-xl w-full" modal position="top"
            v-model:visible="deletingPermissionsVisible" @hide="apiTokenBeingDeleted = null">
            <template #default>
                Você tem certeza que deseja de apagar este Token de API?
            </template>

            <template #footer>
                <Button label="Cancelar" class="bg-tertiary-500 border-tertiary-500" size="small"
                    @click="deletingPermissionsVisible = false" :disabled="deleteApiTokenForm.processing" />

                <Button icon="pi pi-trash" label="Apagar" severity="danger" size="small"
                    :loading="deleteApiTokenForm.processing" @click="deleteApiToken" />
            </template>
        </Dialog>
    </div>
</template>
