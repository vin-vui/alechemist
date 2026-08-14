<script setup lang="ts">
const client = useSanctumClient()

const form = reactive({ current_password: '', password: '', password_confirmation: '' })
const errors = ref<Record<string, string[]>>({})
// Un echec sans detail de validation (reseau, 419, 500) laissait le formulaire
// immobile et muet : on le dit.
const failed = ref(false)
const saved = ref(false)
const pending = ref(false)

async function submit() {
  pending.value = true
  errors.value = {}
  failed.value = false
  saved.value = false
  try {
    await client('/user/password', { method: 'PUT', body: form })
    form.current_password = ''
    form.password = ''
    form.password_confirmation = ''
    saved.value = true
  } catch (e) {
    errors.value = extractErrors(e)
    failed.value = Object.keys(errors.value).length === 0
  } finally {
    pending.value = false
  }
}
</script>

<template>
  <form class="panel profile-section" @submit.prevent="submit">
    <h2 class="section-title">{{ $t('Update Password') }}</h2>
    <p v-if="failed" class="banner-error">{{ $t('Action failed') }}</p>
    <div class="field">
      <label class="field__label" for="current-password">{{ $t('Current Password') }}</label>
      <input id="current-password" v-model="form.current_password" type="password" autocomplete="current-password" class="field__input" />
      <p v-if="errors.current_password" class="field__error">{{ errors.current_password[0] }}</p>
    </div>
    <div class="field">
      <label class="field__label" for="new-password">{{ $t('New Password') }}</label>
      <input id="new-password" v-model="form.password" type="password" autocomplete="new-password" class="field__input" />
      <p v-if="!errors.password" class="field__hint">{{ $t('Password rule') }}</p>
      <p v-else class="field__error">{{ errors.password[0] }}</p>
    </div>
    <div class="field">
      <label class="field__label" for="password-confirmation">{{ $t('Confirm Password') }}</label>
      <input id="password-confirmation" v-model="form.password_confirmation" type="password" autocomplete="new-password" class="field__input" />
      <p v-if="errors.password_confirmation" class="field__error">{{ errors.password_confirmation[0] }}</p>
    </div>
    <div class="profile-section__actions">
      <button type="submit" class="btn btn-primary" :disabled="pending">{{ $t('Change password') }}</button>
      <span v-if="saved" class="profile-section__saved">{{ $t('Saved.') }}</span>
    </div>
  </form>
</template>

<style scoped>
.profile-section {
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 0.875rem;
}






.profile-section__actions {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.profile-section__saved {
  font-size: var(--text-label);
  color: var(--color-malt);
}
</style>
