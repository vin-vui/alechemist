<script setup lang="ts">
import type { Brewing } from '~/types/api'

const route = useRoute()
const recipeId = Number(route.params.recipe)
const brewingId = Number(route.params.brewing)
const { data: brewing } = await useAsyncData(`brewing-${brewingId}`, () => apiGet<Brewing>(`/api/brewings/${brewingId}`))

if (brewing.value && brewing.value.recipe_id !== recipeId) {
  throw createError({ statusCode: 404 })
}

const note = ref(brewing.value?.note ?? '')

async function save() {
  await apiSend(`/api/brewings/${brewingId}`, 'PATCH', { note: note.value })
  brewing.value!.note = note.value
  await navigateTo(brewingPath(brewing.value!))
}

async function destroyBrewing() {
  if (!confirm(useNuxtApp().$i18n.t('Are you sure?'))) return
  await apiSend(`/api/brewings/${brewingId}`, 'DELETE')
  await navigateTo('/recipes')
}
</script>

<template>
  <div v-if="brewing" class="mx-auto max-w-md space-y-4">
    <NuxtLink :to="brewingPath(brewing)" class="text-sm underline">&larr; {{ $t('Back') }}</NuxtLink>
    <h1 class="font-shangrila text-2xl">{{ $t('Note') }} - {{ brewing.name }}</h1>
    <textarea v-model="note" rows="10" class="w-full rounded border-gray-300" />
    <div class="flex justify-between">
      <button type="button" class="btn-yellow rounded" @click="save">{{ $t('Save') }}</button>
      <button type="button" class="text-sm text-red-600 underline" @click="destroyBrewing">{{ $t('Delete') }}</button>
    </div>
  </div>
</template>
