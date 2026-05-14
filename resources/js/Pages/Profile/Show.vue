<script setup>
import SectionBorder from '@/Components/Common/SectionBorder.vue';
import DeleteUserForm from '@/Components/Profile/DeleteUserForm.vue';
import LogoutOtherBrowserSessionsForm from '@/Components/Profile/LogoutOtherBrowserSessionsForm.vue';
import TwoFactorAuthenticationForm from '@/Components/Profile/TwoFactorAuthenticationForm.vue';
import UpdatePasswordForm from '@/Components/Profile/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from '@/Components/Profile/UpdateProfileInformationForm.vue';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    confirmsTwoFactorAuthentication: Boolean,
    sessions: Array,
});
</script>

<template>
    <AppLayout title="Perfil">
        <div>
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                <div v-if="$page.props.jetstream.canUpdateProfileInformation">
                    <UpdateProfileInformationForm :user="$page.props.auth.user" />

                    <SectionBorder />
                </div>

                <div v-if="$page.props.jetstream.canUpdatePassword">
                    <UpdatePasswordForm class="mt-10 sm:mt-0" />

                    <SectionBorder />
                </div>

                <div v-if="$page.props.jetstream.canManageTwoFactorAuthentication">
                    <TwoFactorAuthenticationForm :requires-confirmation="confirmsTwoFactorAuthentication"
                        class="mt-10 sm:mt-0" />

                    <SectionBorder />
                </div>

                <LogoutOtherBrowserSessionsForm :sessions="sessions" class="mt-10 sm:mt-0" />

                <template v-if="$page.props.jetstream.hasAccountDeletionFeatures">
                    <SectionBorder />

                    <DeleteUserForm class="mt-10 sm:mt-0" />
                </template>
            </div>
        </div>
    </AppLayout>
</template>
