<script setup lang="ts">
import type { Brewing } from '~/types/api'

const route = useRoute()
const recipeId = Number(route.params.recipe)
const brewingId = Number(route.params.brewing)
const { data: brewing, error } = await useAsyncData(
  `brewing-${brewingId}`,
  () => apiGet<Brewing>(`/api/brewings/${brewingId}`),
  { deep: true },
)
if (error.value) {
  throw createError({ statusCode: error.value.statusCode ?? 500, statusMessage: error.value.statusMessage ?? error.value.message })
}
if (brewing.value && brewing.value.recipe_id !== recipeId) {
  throw createError({ statusCode: 404 })
}

const note = ref(brewing.value?.note ?? '')
const mutationError = ref(false)

async function save() {
  try {
    await apiSend(`/api/brewings/${brewingId}`, 'PATCH', { note: note.value })
    brewing.value!.note = note.value
    await navigateTo(brewingPath(brewing.value!))
  } catch {
    mutationError.value = true
  }
}

async function destroyBrewing() {
  if (!confirm(useNuxtApp().$i18n.t('Are you sure?'))) return
  await apiSend(`/api/brewings/${brewingId}`, 'DELETE')
  await navigateTo('/recipes')
}
</script>

<template>
  <div v-if="brewing" class="note-page">
    <NuxtLink :to="brewingPath(brewing)" class="note-page__back">&larr; {{ $t('Back') }}</NuxtLink>
    <h1 class="note-page__title">{{ $t('Note') }} - {{ brewing.name }}</h1>
    <p v-if="mutationError" class="banner-error">{{ $t('Note not saved') }}</p>
    <textarea v-model="note" rows="10" class="note-page__textarea" />
    <div class="note-page__actions">
      <button type="button" class="btn btn-primary" @click="save">{{ $t('Save') }}</button>
      <button type="button" class="btn btn-danger" @click="destroyBrewing">{{ $t('delete this brewing') }}</button>
    </div>
  </div>
</template>

<style scoped>
.note-page {
  max-width: 28rem;
  margin-inline: auto;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.note-page__back {
  align-self: flex-start;
  font-size: var(--text-label);
  color: var(--color-text-dim);
}

.note-page__back:hover {
  color: var(--color-text);
}

.note-page__title {
  font-family: var(--font-display);
  text-transform: uppercase;
  letter-spacing: -0.02em;
  font-weight: 800;
  font-size: 1.5rem;
}

.note-page__textarea {
  background-color: var(--color-panel);
  border: 1px solid var(--color-rule);
  border-radius: var(--radius);
  padding: 0.75rem 0.875rem;
  color: var(--color-text);
  font-family: var(--font-sans);
  resize: vertical;
}

.note-page__actions {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
}
</style>
