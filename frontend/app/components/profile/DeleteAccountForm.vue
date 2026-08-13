<script setup lang="ts">
const password = ref('')
const errors = ref<Record<string, string[]>>({})
const askPassword = ref(false)
const pending = ref(false)

async function destroyAccount() {
  pending.value = true
  errors.value = {}
  try {
    await apiSend('/api/user', 'DELETE', { password: password.value })
    window.location.href = '/login'
  } catch (e) {
    errors.value = extractErrors(e)
  } finally {
    pending.value = false
  }
}
</script>

<template>
  <div class="panel profile-section profile-section--danger">
    <h2 class="profile-section__title profile-section__title--danger">{{ $t('Delete Account') }}</h2>
    <p class="profile-section__hint">{{ $t('Once your account is deleted, all of its resources and data will be permanently deleted.') }}</p>
    <button v-if="!askPassword" type="button" class="btn btn-danger" @click="askPassword = true">
      {{ $t('Delete Account') }}
    </button>
    <form v-else class="delete-account__form" @submit.prevent="destroyAccount">
      <div class="field delete-account__field">
        <label class="field__label" for="delete-password">{{ $t('Password') }}</label>
        <input id="delete-password" v-model="password" type="password" class="field__input" />
        <p v-if="errors.password" class="field__error">{{ errors.password[0] }}</p>
      </div>
      <button type="submit" class="btn btn-danger" :disabled="pending">{{ $t('Confirm') }}</button>
    </form>
  </div>
</template>

<style scoped>
.profile-section {
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 0.875rem;
}

.profile-section--danger {
  border-color: var(--color-ember);
  border-width: 2px;
}

.profile-section__title {
  font-family: var(--font-display);
  text-transform: uppercase;
  letter-spacing: -0.02em;
  font-weight: 700;
  font-size: 1.125rem;
}

.profile-section__title--danger {
  color: var(--color-ember);
}

.profile-section__hint {
  font-size: var(--text-body);
  color: var(--color-text-dim);
}

.delete-account__form {
  display: flex;
  align-items: flex-end;
  gap: 0.5rem;
}

.delete-account__field {
  flex: 1;
}

.field__label {
  font-size: var(--text-label);
  color: var(--color-text-dim);
}

.field__input {
  height: 48px;
  width: 100%;
  background-color: var(--color-panel);
  border: 1px solid var(--color-rule);
  border-radius: var(--radius);
  padding-inline: 0.875rem;
  color: var(--color-text);
  font-family: var(--font-sans);
}

.field__error {
  font-size: var(--text-label);
  color: var(--color-ember);
}
</style>
