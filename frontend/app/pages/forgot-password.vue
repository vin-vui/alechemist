<script setup lang="ts">
definePageMeta({ layout: 'guest', sanctum: { excluded: true } })

const client = useSanctumClient()

const email = ref('')
const errors = ref<Record<string, string[]>>({})
const pending = ref(false)
const status = ref(false)

async function submit() {
  pending.value = true
  errors.value = {}
  status.value = false
  try {
    await client('/forgot-password', { method: 'POST', body: { email: email.value } })
    status.value = true
  } catch (e) {
    errors.value = extractErrors(e)
  } finally {
    pending.value = false
  }
}
</script>

<template>
  <div class="mx-auto mt-10 w-full max-w-md rounded bg-white p-6 shadow">
    <img src="/assets/logo/alechemist@512.png" alt="AleChemist" class="mx-auto mb-4 size-16 rounded" />
    <h1 class="mb-6 text-center font-shangrila text-2xl">{{ $t('Forgot your password?') }}</h1>
    <p v-if="status" class="mb-4 text-sm text-green-600">{{ $t('We have emailed your password reset link!') }}</p>
    <form class="space-y-4" @submit.prevent="submit">
      <div>
        <label class="block text-sm" for="email">{{ $t('Email') }}</label>
        <input id="email" v-model="email" type="email" required autocomplete="username" class="mt-1 w-full rounded border-gray-300" />
        <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email[0] }}</p>
      </div>
      <button type="submit" class="btn-yellow w-full rounded" :disabled="pending">{{ $t('Email Password Reset Link') }}</button>
    </form>
    <div class="mt-4 text-center text-sm">
      <NuxtLink to="/login" class="underline">{{ $t('Log in') }}</NuxtLink>
    </div>
  </div>
</template>
