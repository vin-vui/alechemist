<script setup lang="ts">
const password = ref('')
const errors = ref<Record<string, string[]>>({})
const askPassword = ref(false)
const pending = ref(false)

async function destroyAccount() {
  pending.value = true
  errors.value = {}
  try {
    await apiSend('/api/user', 'DELETE', { password: password.value })
    window.location.href = '/login'
  } catch (e) {
    errors.value = extractErrors(e)
  } finally {
    pending.value = false
  }
}
</script>

<template>
  <div class="space-y-3 rounded border-2 border-red-200 bg-white p-4 shadow">
    <h2 class="font-shangrila text-lg text-red-600">{{ $t('Delete Account') }}</h2>
    <p class="text-sm text-gray-600">{{ $t('Once your account is deleted, all of its resources and data will be permanently deleted.') }}</p>
    <button v-if="!askPassword" type="button" class="rounded bg-red-600 px-3 py-1.5 text-white" @click="askPassword = true">
      {{ $t('Delete Account') }}
    </button>
    <form v-else class="flex items-end gap-2" @submit.prevent="destroyAccount">
      <div class="flex-1">
        <label class="block text-sm" for="delete-password">{{ $t('Password') }}</label>
        <input id="delete-password" v-model="password" type="password" class="mt-1 w-full rounded border-gray-300" />
        <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password[0] }}</p>
      </div>
      <button type="submit" class="rounded bg-red-600 px-3 py-1.5 text-white" :disabled="pending">{{ $t('Confirm') }}</button>
    </form>
  </div>
</template>
