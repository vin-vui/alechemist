<script setup lang="ts">
import type { Brewing } from '~/types/api'

const route = useRoute()
const recipeId = Number(route.params.recipe)
const form = reactive({
  name: '',
  date_start: new Date().toISOString().slice(0, 10),
  note: '',
})
const errors = ref<Record<string, string[]>>({})

async function submit() {
  errors.value = {}
  try {
    const brewing = await apiSend<Brewing>(`/api/recipes/${recipeId}/brewings`, 'POST', { ...form })
    await navigateTo(`/recipes/${recipeId}/brewings/${brewing.id}/preparation`)
  } catch (e) {
    errors.value = extractErrors(e)
  }
}
</script>

<template>
  <div class="mx-auto max-w-md">
    <h1 class="mb-4 font-shangrila text-2xl">{{ $t('New Brewing') }}</h1>
    <form class="space-y-4 rounded bg-white p-6 shadow" @submit.prevent="submit">
      <div>
        <label class="block text-sm" for="name">{{ $t('Name') }}</label>
        <input id="name" v-model="form.name" required maxlength="255" class="mt-1 w-full rounded border-gray-300" />
        <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name[0] }}</p>
      </div>
      <div>
        <label class="block text-sm" for="date_start">{{ $t('Date') }}</label>
        <input id="date_start" v-model="form.date_start" type="date" required class="mt-1 w-full rounded border-gray-300" />
        <p v-if="errors.date_start" class="mt-1 text-sm text-red-600">{{ errors.date_start[0] }}</p>
      </div>
      <div>
        <label class="block text-sm" for="note">{{ $t('Note') }}</label>
        <textarea id="note" v-model="form.note" rows="8" class="mt-1 w-full rounded border-gray-300" />
      </div>
      <button type="submit" class="btn-yellow w-full rounded">{{ $t('Save & Start') }}</button>
    </form>
  </div>
</template>
