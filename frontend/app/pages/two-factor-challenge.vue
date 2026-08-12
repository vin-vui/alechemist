<script setup lang="ts">
definePageMeta({ layout: 'guest', sanctum: { excluded: true } })

const client = useSanctumClient()
const { refreshIdentity } = useSanctumAuth()

const useRecovery = ref(false)
const form = reactive({ code: '', recovery_code: '' })
const errors = ref<Record<string, string[]>>({})

async function submit() {
  errors.value = {}
  try {
    const body = useRecovery.value ? { recovery_code: form.recovery_code } : { code: form.code }
    await client('/two-factor-challenge', { method: 'POST', body })
    await refreshIdentity()
    await navigateTo('/')
  } catch (e) {
    errors.value = extractErrors(e)
  }
}
</script>

<template>
  <div class="mx-auto mt-10 w-full max-w-md rounded bg-white p-6 shadow">
    <h1 class="mb-6 text-center font-shangrila text-2xl">{{ $t('Two Factor Authentication') }}</h1>
    <form class="space-y-4" @submit.prevent="submit">
      <div v-if="!useRecovery">
        <label class="block text-sm" for="code">{{ $t('Code') }}</label>
        <input id="code" v-model="form.code" inputmode="numeric" autocomplete="one-time-code" class="mt-1 w-full rounded border-gray-300" />
        <p v-if="errors.code" class="mt-1 text-sm text-red-600">{{ errors.code[0] }}</p>
      </div>
      <div v-else>
        <label class="block text-sm" for="recovery_code">{{ $t('Recovery Code') }}</label>
        <input id="recovery_code" v-model="form.recovery_code" class="mt-1 w-full rounded border-gray-300" />
        <p v-if="errors.recovery_code" class="mt-1 text-sm text-red-600">{{ errors.recovery_code[0] }}</p>
      </div>
      <button type="submit" class="btn-yellow w-full rounded">{{ $t('Log in') }}</button>
      <button type="button" class="w-full text-sm underline" @click="useRecovery = !useRecovery">
        {{ useRecovery ? $t('Use an authentication code') : $t('Use a recovery code') }}
      </button>
    </form>
  </div>
</template>
