<script setup lang="ts">
import type { Recipe } from '~/types/api'

const file = ref<File | null>(null)
const errors = ref<Record<string, string[]>>({})
const pending = ref(false)

function onFileChange(event: Event) {
  file.value = (event.target as HTMLInputElement).files?.[0] ?? null
}

async function submit() {
  if (!file.value) return
  pending.value = true
  errors.value = {}
  const body = new FormData()
  body.append('file', file.value)
  try {
    const recipe = await apiSend<Recipe>('/api/recipes/import', 'POST', body)
    await navigateTo(`/recipes/${recipe.id}`)
  } catch (e) {
    errors.value = extractErrors(e)
  } finally {
    pending.value = false
  }
}
</script>

<template>
  <div class="mx-auto max-w-md">
    <h1 class="mb-4 font-shangrila text-2xl">{{ $t('Import File') }}</h1>
    <form class="space-y-4 rounded bg-white p-6 shadow" @submit.prevent="submit">
      <p class="text-sm text-gray-500">{{ $t('File BeerXML Format Only') }}</p>
      <label class="block cursor-pointer rounded border-2 border-dashed border-gray-300 p-6 text-center hover:border-xanthous">
        <span v-if="!file">{{ $t('Upload a file') }} ({{ $t('XML up to 2MB') }})</span>
        <span v-else class="font-semibold">{{ file.name }}</span>
        <input type="file" accept=".xml,text/xml" class="hidden" @change="onFileChange" />
      </label>
      <p v-if="errors.file" class="text-sm text-red-600">{{ errors.file[0] }}</p>
      <button type="submit" class="btn-yellow w-full rounded" :disabled="pending || !file">{{ $t('Import') }}</button>
    </form>
  </div>
</template>
