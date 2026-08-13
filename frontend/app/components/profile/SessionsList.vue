<script setup lang="ts">
interface SessionRow {
  ip_address: string | null
  user_agent: string | null
  last_active_at: string
  is_current_device: boolean
}

const { data: sessions, refresh } = await useAsyncData('sessions', () => apiGet<SessionRow[]>('/api/user/sessions'))
const password = ref('')
const errors = ref<Record<string, string[]>>({})
const askPassword = ref(false)

async function logoutOthers() {
  errors.value = {}
  try {
    await apiSend('/api/user/sessions/logout-others', 'POST', { password: password.value })
    askPassword.value = false
    password.value = ''
    await refresh()
  } catch (e) {
    errors.value = extractErrors(e)
  }
}
</script>

<template>
  <div class="space-y-3 rounded bg-white p-4 shadow">
    <h2 class="font-shangrila text-lg">{{ $t('Browser Sessions') }}</h2>
    <ul class="space-y-2 text-sm">
      <li v-for="(session, index) in sessions ?? []" :key="index" class="flex items-center justify-between">
        <span class="truncate">{{ session.ip_address }} - {{ session.user_agent }}</span>
        <span v-if="session.is_current_device" class="info-label-yellow ml-2 shrink-0">{{ $t('This device') }}</span>
        <span v-else class="ml-2 shrink-0 text-gray-500">{{ formatDate(session.last_active_at) }}</span>
      </li>
    </ul>
    <button v-if="!askPassword" type="button" class="btn-yellow rounded" @click="askPassword = true">
      {{ $t('Log Out Other Browser Sessions') }}
    </button>
    <form v-else class="flex items-end gap-2" @submit.prevent="logoutOthers">
      <div class="flex-1">
        <label class="block text-sm" for="sessions-password">{{ $t('Password') }}</label>
        <input id="sessions-password" v-model="password" type="password" class="mt-1 w-full rounded border-gray-300" />
        <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password[0] }}</p>
      </div>
      <button type="submit" class="btn-yellow rounded">{{ $t('Confirm') }}</button>
    </form>
  </div>
</template>
