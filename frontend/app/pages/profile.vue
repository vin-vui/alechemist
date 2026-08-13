<script setup lang="ts">
// La deconnexion vit ici (accessible depuis le profil) et non plus dans la barre
// laterale, cf. docs/design-system.md (Mise en page) et AppSidebar.vue.
const { logout } = useSanctumAuth()
</script>

<template>
  <div class="profile-page">
    <div class="profile-page__header">
      <h1 class="profile-page__title">{{ $t('Profile') }}</h1>
      <button type="button" class="btn btn-secondary" @click="logout()">{{ $t('Log Out') }}</button>
    </div>
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

.profile-page__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
}

.profile-page__title {
  font-family: var(--font-display);
  text-transform: uppercase;
  letter-spacing: -0.02em;
  font-weight: 800;
  font-size: 1.5rem;
}
</style>
