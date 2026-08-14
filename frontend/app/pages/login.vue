<script setup lang="ts">
// `excluded: true` sortait la page du middleware global : un utilisateur deja
// connecte pouvait ouvrir /login, y poster le formulaire, et rester bloque dessus.
// Sans `excluded`, le middleware reconnait /login comme page de visiteur et le
// renvoie au tableau de bord.
definePageMeta({ layout: 'guest' })

const { t } = useI18n()
const client = useSanctumClient()
const { refreshIdentity } = useSanctumAuth()

const form = reactive({ email: '', password: '', remember: false })
const errors = ref<Record<string, string[]>>({})
const failure = ref<string | null>(null)
const pending = ref(false)

useHead({ title: () => t('Log in') })

async function submit() {
  pending.value = true
  errors.value = {}
  failure.value = null
  try {
    // Fortify repond 204 sans corps dans certains cas : `response.two_factor`
    // levait alors un TypeError, attrape plus bas, et l'utilisateur restait
    // devant un formulaire immobile sans le moindre message.
    const response = await client<{ two_factor?: boolean } | null>('/login', { method: 'POST', body: form })
    if (response?.two_factor) {
      await navigateTo('/two-factor-challenge')
    } else {
      await refreshIdentity()
      await navigateTo('/')
    }
  } catch (e) {
    errors.value = extractErrors(e)
    // Une erreur sans detail de validation (reseau, 419, 500) ne doit pas se
    // traduire par un ecran qui ne bouge pas : on le dit.
    if (Object.keys(errors.value).length === 0) {
      failure.value = t('Login failed')
    }
  } finally {
    pending.value = false
  }
}
</script>

<template>
  <div class="panel auth-card">
    <img src="/assets/logo/alechemist@512.png" alt="AleChemist" class="auth-card__logo" />
    <h1 class="auth-card__title">{{ $t('Log in') }}</h1>
    <p v-if="failure" class="banner-error">{{ failure }}</p>
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
        <input v-model="form.remember" type="checkbox" class="checkbox" /> {{ $t('Remember me') }}
      </label>
      <button type="submit" class="btn btn-primary auth-card__submit" :disabled="pending">{{ $t('Log in') }}</button>
    </form>
    <div class="auth-card__links">
      <NuxtLink to="/forgot-password" class="auth-card__link">{{ $t('Forgot your password?') }}</NuxtLink>
      <NuxtLink to="/register" class="auth-card__link">{{ $t('Register') }}</NuxtLink>
    </div>
  </div>
</template>

