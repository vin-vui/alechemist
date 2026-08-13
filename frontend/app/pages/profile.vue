<script setup lang="ts">
// La deconnexion vit ici (accessible depuis le profil) et non plus dans la barre
// laterale, cf. docs/design-system.md (Mise en page) et AppSidebar.vue.
const { logout } = useSanctumAuth()
</script>

<template>
  <div class="mx-auto max-w-2xl space-y-4">
    <div class="flex items-center justify-between">
      <h1 class="font-shangrila text-2xl">{{ $t('Profile') }}</h1>
      <button type="button" class="btn btn-secondary" @click="logout()">{{ $t('Log Out') }}</button>
    </div>
    <ProfileInfoForm />
    <PasswordForm />
    <TwoFactorForm />
    <SessionsList />
    <DeleteAccountForm />
  </div>
</template>
