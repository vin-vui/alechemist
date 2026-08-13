<script setup lang="ts">
const client = useSanctumClient()

const form = reactive({ current_password: '', password: '', password_confirmation: '' })
const errors = ref<Record<string, string[]>>({})
const saved = ref(false)
const pending = ref(false)

async function submit() {
  pending.value = true
  errors.value = {}
  saved.value = false
  try {
    await client('/user/password', { method: 'PUT', body: form })
    form.current_password = ''
    form.password = ''
    form.password_confirmation = ''
    saved.value = true
  } catch (e) {
    errors.value = extractErrors(e)
  } finally {
    pending.value = false
  }
}
</script>

<template>
  <form class="space-y-3 rounded bg-white p-4 shadow" @submit.prevent="submit">
    <h2 class="font-shangrila text-lg">{{ $t('Update Password') }}</h2>
    <div>
      <label class="block text-sm" for="current-password">{{ $t('Current Password') }}</label>
      <input id="current-password" v-model="form.current_password" type="password" autocomplete="current-password" class="mt-1 w-full rounded border-gray-300" />
      <p v-if="errors.current_password" class="mt-1 text-sm text-red-600">{{ errors.current_password[0] }}</p>
    </div>
    <div>
      <label class="block text-sm" for="new-password">{{ $t('New Password') }}</label>
      <input id="new-password" v-model="form.password" type="password" autocomplete="new-password" class="mt-1 w-full rounded border-gray-300" />
      <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password[0] }}</p>
    </div>
    <div>
      <label class="block text-sm" for="password-confirmation">{{ $t('Confirm Password') }}</label>
      <input id="password-confirmation" v-model="form.password_confirmation" type="password" autocomplete="new-password" class="mt-1 w-full rounded border-gray-300" />
      <p v-if="errors.password_confirmation" class="mt-1 text-sm text-red-600">{{ errors.password_confirmation[0] }}</p>
    </div>
    <div class="flex items-center gap-3">
      <button type="submit" class="btn-yellow rounded" :disabled="pending">{{ $t('Save') }}</button>
      <span v-if="saved" class="text-sm text-gray-500">{{ $t('Saved.') }}</span>
    </div>
  </form>
</template>
