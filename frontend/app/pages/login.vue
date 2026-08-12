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
  <div class="mx-auto mt-10 w-full max-w-md rounded bg-white p-6 shadow">
    <img src="/assets/logo/alechemist@512.png" alt="AleChemist" class="mx-auto mb-4 size-16 rounded" />
    <h1 class="mb-6 text-center font-shangrila text-2xl">{{ $t('Log in') }}</h1>
    <form class="space-y-4" @submit.prevent="submit">
      <div>
        <label class="block text-sm" for="email">{{ $t('Email') }}</label>
        <input id="email" v-model="form.email" type="email" required autocomplete="username" class="mt-1 w-full rounded border-gray-300" />
        <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email[0] }}</p>
      </div>
      <div>
        <label class="block text-sm" for="password">{{ $t('Password') }}</label>
        <input id="password" v-model="form.password" type="password" required autocomplete="current-password" class="mt-1 w-full rounded border-gray-300" />
        <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password[0] }}</p>
      </div>
      <label class="flex items-center gap-2 text-sm">
        <input v-model="form.remember" type="checkbox" class="rounded" /> {{ $t('Remember me') }}
      </label>
      <button type="submit" class="btn-yellow w-full rounded" :disabled="pending">{{ $t('Log in') }}</button>
    </form>
    <div class="mt-4 flex justify-between text-sm">
      <NuxtLink to="/forgot-password" class="underline">{{ $t('Forgot your password?') }}</NuxtLink>
      <NuxtLink to="/register" class="underline">{{ $t('Register') }}</NuxtLink>
    </div>
  </div>
</template>
