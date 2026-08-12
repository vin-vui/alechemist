<script setup lang="ts">
definePageMeta({ layout: 'guest', sanctum: { excluded: true } })

const client = useSanctumClient()
const { refreshIdentity } = useSanctumAuth()

const form = reactive({ name: '', email: '', password: '', password_confirmation: '' })
const errors = ref<Record<string, string[]>>({})
const pending = ref(false)

async function submit() {
  pending.value = true
  errors.value = {}
  try {
    await client('/register', { method: 'POST', body: form })
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
  <div class="mx-auto mt-10 w-full max-w-md rounded bg-white p-6 shadow">
    <img src="/assets/logo/alechemist@512.png" alt="AleChemist" class="mx-auto mb-4 size-16 rounded" />
    <h1 class="mb-6 text-center font-shangrila text-2xl">{{ $t('Register') }}</h1>
    <form class="space-y-4" @submit.prevent="submit">
      <div>
        <label class="block text-sm" for="name">{{ $t('Name') }}</label>
        <input id="name" v-model="form.name" type="text" required autocomplete="name" class="mt-1 w-full rounded border-gray-300" />
        <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name[0] }}</p>
      </div>
      <div>
        <label class="block text-sm" for="email">{{ $t('Email') }}</label>
        <input id="email" v-model="form.email" type="email" required autocomplete="username" class="mt-1 w-full rounded border-gray-300" />
        <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email[0] }}</p>
      </div>
      <div>
        <label class="block text-sm" for="password">{{ $t('Password') }}</label>
        <input id="password" v-model="form.password" type="password" required autocomplete="new-password" class="mt-1 w-full rounded border-gray-300" />
        <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password[0] }}</p>
      </div>
      <div>
        <label class="block text-sm" for="password_confirmation">{{ $t('Confirm Password') }}</label>
        <input id="password_confirmation" v-model="form.password_confirmation" type="password" required autocomplete="new-password" class="mt-1 w-full rounded border-gray-300" />
        <p v-if="errors.password_confirmation" class="mt-1 text-sm text-red-600">{{ errors.password_confirmation[0] }}</p>
      </div>
      <button type="submit" class="btn-yellow w-full rounded" :disabled="pending">{{ $t('Register') }}</button>
    </form>
    <div class="mt-4 text-center text-sm">
      <NuxtLink to="/login" class="underline">{{ $t('Already registered?') }}</NuxtLink>
    </div>
  </div>
</template>
