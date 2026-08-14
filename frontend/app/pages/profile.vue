<script setup lang="ts">
// La deconnexion vit ici (accessible depuis le profil) et non plus dans la barre
// laterale, cf. docs/design-system.md (Mise en page) et AppSidebar.vue.
const { logout } = useSanctumAuth()
const { t } = useI18n()

useHead({ title: () => t('Profile') })
</script>

<template>
  <div class="profile-page">
    <PageHeader :title="$t('Profile')">
      <template #action>
        <button
          type="button"
          class="btn btn-secondary"
          :aria-label="$t('Log Out')"
          :title="$t('Log Out')"
          @click="logout()"
        >
          <AppIcon name="logout" />
          <span>{{ $t('Log Out') }}</span>
        </button>
      </template>
    </PageHeader>
    <ProfileInfoForm />
    <PasswordForm />
    <TwoFactorForm />
    <SessionsList />
    <DeleteAccountForm />
  </div>
</template>

<style scoped>
.profile-page {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

</style>
