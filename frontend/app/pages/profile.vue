<template>
  <div class="mx-auto max-w-2xl space-y-4">
    <h1 class="font-shangrila text-2xl">{{ $t('Profile') }}</h1>
    <ProfileInfoForm />
    <PasswordForm />
    <TwoFactorForm />
    <SessionsList />
    <DeleteAccountForm />
  </div>
</template>
