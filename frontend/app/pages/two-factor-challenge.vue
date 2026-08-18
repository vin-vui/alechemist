<script setup lang="ts">
definePageMeta({ layout: 'guest', sanctum: { guestOnly: true } })

const client = useSanctumClient()
const { refreshIdentity } = useSanctumAuth()

const useRecovery = ref(false)
const form = reactive({ code: '', recovery_code: '' })
const errors = ref<Record<string, string[]>>({})
// Un echec sans detail de validation (reseau, 419, 500) laissait le formulaire
// immobile et muet : on le dit.
const failure = ref<string | null>(null)
const pending = ref(false)

async function submit() {
  pending.value = true
  errors.value = {}
  failure.value = null
  try {
    const body = useRecovery.value ? { recovery_code: form.recovery_code } : { code: form.code }
    await client('/two-factor-challenge', { method: 'POST', body })
    await refreshIdentity()
    await navigateTo('/')
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
    <h1 class="auth-card__title">{{ $t('Two Factor Authentication') }}</h1>
    <p v-if="failure" class="banner-error">{{ $t(failure) }}</p>
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

