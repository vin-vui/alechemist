<script setup lang="ts">
const client = useSanctumClient()
const { refreshIdentity } = useSanctumAuth()
const user = useCurrentUser()

const form = reactive({ name: user.value?.name ?? '', email: user.value?.email ?? '' })
const errors = ref<Record<string, string[]>>({})
const saved = ref(false)
const pending = ref(false)

async function submit() {
  pending.value = true
  errors.value = {}
  saved.value = false
  try {
    await client('/user/profile-information', { method: 'PUT', body: form })
    await refreshIdentity()
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
    <h2 class="font-shangrila text-lg">{{ $t('Profile Information') }}</h2>
    <div>
      <label class="block text-sm" for="profile-name">{{ $t('Name') }}</label>
      <input id="profile-name" v-model="form.name" class="mt-1 w-full rounded border-gray-300" />
      <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name[0] }}</p>
    </div>
    <div>
      <label class="block text-sm" for="profile-email">{{ $t('Email') }}</label>
      <input id="profile-email" v-model="form.email" type="email" class="mt-1 w-full rounded border-gray-300" />
      <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email[0] }}</p>
    </div>
    <div class="flex items-center gap-3">
      <button type="submit" class="btn-yellow rounded" :disabled="pending">{{ $t('Save') }}</button>
      <span v-if="saved" class="text-sm text-gray-500">{{ $t('Saved.') }}</span>
    </div>
  </form>
</template>
