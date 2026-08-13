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
  <div class="import-page">
    <h1 class="import-page__title">{{ $t('Import File') }}</h1>
    <form class="panel import-page__form" @submit.prevent="submit">
      <p class="import-page__hint">{{ $t('File BeerXML Format Only') }}</p>
      <label class="import-page__dropzone">
        <span v-if="!file">{{ $t('Upload a file') }} ({{ $t('XML up to 2MB') }})</span>
        <span v-else class="import-page__filename">{{ file.name }}</span>
        <input type="file" accept=".xml,text/xml" class="import-page__file-input" @change="onFileChange" />
      </label>
      <p v-if="errors.file" class="import-page__error">{{ errors.file[0] }}</p>
      <button type="submit" class="btn btn-primary import-page__submit" :disabled="pending || !file">{{ $t('Import') }}</button>
    </form>
  </div>
</template>

<style scoped>
.import-page {
  max-width: 28rem;
  margin-inline: auto;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.import-page__title {
  font-family: var(--font-display);
  text-transform: uppercase;
  letter-spacing: -0.02em;
  font-weight: 800;
  font-size: 1.5rem;
}

.import-page__form {
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.import-page__hint {
  font-size: var(--text-label);
  color: var(--color-text-dim);
}

.import-page__dropzone {
  position: relative;
  display: block;
  cursor: pointer;
  border: 2px dashed var(--color-rule);
  border-radius: var(--radius);
  padding: 2rem 1rem;
  text-align: center;
  color: var(--color-text-dim);
  min-height: 96px;
}

.import-page__dropzone:hover {
  border-color: var(--color-signal);
}

.import-page__filename {
  font-weight: 600;
  color: var(--color-text);
}

.import-page__file-input {
  position: absolute;
  inset: 0;
  opacity: 0;
  cursor: pointer;
}

.import-page__error {
  font-size: var(--text-label);
  color: var(--color-ember);
}

.import-page__submit {
  width: 100%;
}
</style>
