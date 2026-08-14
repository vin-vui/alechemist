<script setup lang="ts">
definePageMeta({ layout: 'guest', sanctum: { guestOnly: true } })

const { t } = useI18n()
const client = useSanctumClient()
const { refreshIdentity } = useSanctumAuth()

const form = reactive({ name: '', email: '', password: '', password_confirmation: '' })
const errors = ref<Record<string, string[]>>({})
// Un echec sans detail de validation (reseau, 419, 500) laissait le formulaire
// immobile et muet : on le dit.
const failed = ref(false)
const pending = ref(false)

useHead({ title: () => t('Register') })

async function submit() {
  pending.value = true
  errors.value = {}
  failed.value = false
  try {
    await client('/register', { method: 'POST', body: form })
    await refreshIdentity()
    await navigateTo('/')
  } catch (e) {
    errors.value = extractErrors(e)
    failed.value = Object.keys(errors.value).length === 0
  } finally {
    pending.value = false
  }
}
</script>

<template>
  <div class="panel auth-card">
    <img src="/assets/logo/alechemist@512.png" alt="AleChemist" class="auth-card__logo" />
    <h1 class="auth-card__title">{{ $t('Register') }}</h1>
    <p v-if="failed" class="banner-error">{{ $t('Action failed') }}</p>
    <form class="auth-card__form" @submit.prevent="submit">
      <div class="field">
        <label class="field__label" for="name">{{ $t('Name') }}</label>
        <input id="name" v-model="form.name" type="text" required autocomplete="name" class="field__input" />
        <p v-if="errors.name" class="field__error">{{ errors.name[0] }}</p>
      </div>
      <div class="field">
        <label class="field__label" for="email">{{ $t('Email') }}</label>
        <input id="email" v-model="form.email" type="email" required autocomplete="username" class="field__input" />
        <p v-if="errors.email" class="field__error">{{ errors.email[0] }}</p>
      </div>
      <div class="field">
        <label class="field__label" for="password">{{ $t('Password') }}</label>
        <input id="password" v-model="form.password" type="password" required autocomplete="new-password" class="field__input" />
        <!-- La regle etait decouverte en echouant a l'envoi. -->
        <p v-if="!errors.password" class="field__hint">{{ $t('Password rule') }}</p>
        <p v-else class="field__error">{{ errors.password[0] }}</p>
      </div>
      <div class="field">
        <label class="field__label" for="password_confirmation">{{ $t('Confirm Password') }}</label>
        <input id="password_confirmation" v-model="form.password_confirmation" type="password" required autocomplete="new-password" class="field__input" />
        <p v-if="errors.password_confirmation" class="field__error">{{ errors.password_confirmation[0] }}</p>
      </div>
      <button type="submit" class="btn btn-primary auth-card__submit" :disabled="pending">{{ $t('Create my account') }}</button>
    </form>
    <div class="auth-card__links auth-card__links--center">
      <NuxtLink to="/login" class="auth-card__link">{{ $t('Already registered?') }}</NuxtLink>
    </div>
  </div>
</template>

