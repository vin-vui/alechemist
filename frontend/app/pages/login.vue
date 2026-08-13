<script setup lang="ts">
definePageMeta({ layout: 'guest', sanctum: { excluded: true } })

const client = useSanctumClient()
const { refreshIdentity } = useSanctumAuth()

const form = reactive({ email: '', password: '', remember: false })
const errors = ref<Record<string, string[]>>({})
const pending = ref(false)

async function submit() {
  pending.value = true
  errors.value = {}
  try {
    const response = await client<{ two_factor: boolean }>('/login', { method: 'POST', body: form })
    if (response.two_factor) {
      await navigateTo('/two-factor-challenge')
    } else {
      await refreshIdentity()
      await navigateTo('/')
    }
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
    <h1 class="auth-card__title">{{ $t('Log in') }}</h1>
    <form class="auth-card__form" @submit.prevent="submit">
      <div class="field">
        <label class="field__label" for="email">{{ $t('Email') }}</label>
        <input id="email" v-model="form.email" type="email" required autocomplete="username" class="field__input" />
        <p v-if="errors.email" class="field__error">{{ errors.email[0] }}</p>
      </div>
      <div class="field">
        <label class="field__label" for="password">{{ $t('Password') }}</label>
        <input id="password" v-model="form.password" type="password" required autocomplete="current-password" class="field__input" />
        <p v-if="errors.password" class="field__error">{{ errors.password[0] }}</p>
      </div>
      <label class="auth-card__remember">
        <input v-model="form.remember" type="checkbox" class="auth-card__checkbox" /> {{ $t('Remember me') }}
      </label>
      <button type="submit" class="btn btn-primary auth-card__submit" :disabled="pending">{{ $t('Log in') }}</button>
    </form>
    <div class="auth-card__links">
      <NuxtLink to="/forgot-password" class="auth-card__link">{{ $t('Forgot your password?') }}</NuxtLink>
      <NuxtLink to="/register" class="auth-card__link">{{ $t('Register') }}</NuxtLink>
    </div>
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

.auth-card__remember {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: var(--text-label);
  color: var(--color-text-dim);
}

.auth-card__checkbox {
  width: 18px;
  height: 18px;
  accent-color: var(--color-signal);
}

.auth-card__submit {
  width: 100%;
}

.auth-card__links {
  display: flex;
  justify-content: space-between;
  font-size: var(--text-label);
}

.auth-card__link {
  color: var(--color-text-dim);
}

.auth-card__link:hover {
  color: var(--color-text);
}
</style>
