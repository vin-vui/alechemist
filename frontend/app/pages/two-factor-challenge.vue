<script setup lang="ts">
definePageMeta({ layout: 'guest', sanctum: { excluded: true } })

const client = useSanctumClient()
const { refreshIdentity } = useSanctumAuth()

const useRecovery = ref(false)
const form = reactive({ code: '', recovery_code: '' })
const errors = ref<Record<string, string[]>>({})
const pending = ref(false)

async function submit() {
  pending.value = true
  errors.value = {}
  try {
    const body = useRecovery.value ? { recovery_code: form.recovery_code } : { code: form.code }
    await client('/two-factor-challenge', { method: 'POST', body })
    await refreshIdentity()
    await navigateTo('/')
  } catch (e) {
    errors.value = extractErrors(e)
  } finally {
    pending.value = false
  }
}
</script>

<template>
  <div class="panel auth-card">
    <img src="/assets/logo/alechemist@512.png" alt="AleChemist" class="auth-card__logo" />
    <h1 class="auth-card__title">{{ $t('Two Factor Authentication') }}</h1>
    <form class="auth-card__form" @submit.prevent="submit">
      <div v-if="!useRecovery" class="field">
        <label class="field__label" for="code">{{ $t('Code') }}</label>
        <input id="code" v-model="form.code" inputmode="numeric" autocomplete="one-time-code" class="field__input" />
        <p v-if="errors.code" class="field__error">{{ errors.code[0] }}</p>
      </div>
      <div v-else class="field">
        <label class="field__label" for="recovery_code">{{ $t('Recovery Code') }}</label>
        <input id="recovery_code" v-model="form.recovery_code" class="field__input" />
        <p v-if="errors.recovery_code" class="field__error">{{ errors.recovery_code[0] }}</p>
      </div>
      <button type="submit" class="btn btn-primary auth-card__submit" :disabled="pending">{{ $t('Log in') }}</button>
      <button type="button" class="auth-card__toggle" @click="useRecovery = !useRecovery">
        {{ useRecovery ? $t('Use an authentication code') : $t('Use a recovery code') }}
      </button>
    </form>
  </div>
</template>

<style scoped>
.auth-card {
  padding: 2rem;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.auth-card__logo {
  width: 3.5rem;
  height: 3.5rem;
  margin-inline: auto;
  border-radius: var(--radius);
}

.auth-card__title {
  font-family: var(--font-display);
  text-transform: uppercase;
  letter-spacing: -0.02em;
  font-weight: 800;
  font-size: 1.5rem;
  text-align: center;
}

.auth-card__form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
}

.field__label {
  font-size: var(--text-label);
  color: var(--color-text-dim);
}

.field__input {
  height: 48px;
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

.auth-card__submit {
  width: 100%;
}

.auth-card__toggle {
  width: 100%;
  min-height: 44px;
  font-size: var(--text-label);
  color: var(--color-text-dim);
}

.auth-card__toggle:hover {
  color: var(--color-text);
}
</style>
