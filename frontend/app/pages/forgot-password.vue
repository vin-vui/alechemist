<script setup lang="ts">
definePageMeta({ layout: 'guest', sanctum: { guestOnly: true } })

const { t } = useI18n()
const client = useSanctumClient()

useHead({ title: () => t('Forgot your password?') })

const email = ref('')
const errors = ref<Record<string, string[]>>({})
// Un echec sans detail de validation (reseau, 419, 500) laissait le formulaire
// immobile et muet : on le dit.
const failure = ref<string | null>(null)
const pending = ref(false)
const status = ref(false)

async function submit() {
  pending.value = true
  errors.value = {}
  failure.value = null
  status.value = false
  try {
    await client('/forgot-password', { method: 'POST', body: { email: email.value } })
    status.value = true
  } catch (e) {
    errors.value = extractErrors(e)
    failure.value = Object.keys(errors.value).length === 0 ? failureMessageKey(e) : null
  } finally {
    pending.value = false
  }
}
</script>

<template>
  <div class="panel auth-card">
    <img src="/assets/logo/alechemist@512.png" alt="AleChemist" class="auth-card__logo" />
    <h1 class="auth-card__title">{{ $t('Forgot your password?') }}</h1>
    <p v-if="failure" class="banner-error">{{ $t(failure) }}</p>
    <p v-if="status" class="auth-card__status">{{ $t('We have emailed your password reset link!') }}</p>
    <form class="auth-card__form" @submit.prevent="submit">
      <div class="field">
        <label class="field__label" for="email">{{ $t('Email') }}</label>
        <input id="email" v-model="email" type="email" required autocomplete="username" class="field__input" />
        <p v-if="errors.email" class="field__error">{{ errors.email[0] }}</p>
      </div>
      <button type="submit" class="btn btn-primary auth-card__submit" :disabled="pending">{{ $t('Email Password Reset Link') }}</button>
    </form>
    <div class="auth-card__links auth-card__links--center">
      <NuxtLink to="/login" class="auth-card__link">{{ $t('Log in') }}</NuxtLink>
    </div>
  </div>
</template>

