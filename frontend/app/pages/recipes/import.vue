<script setup lang="ts">
import type { Recipe } from '~/types/api'

const { t } = useI18n()

const file = ref<File | null>(null)
const errors = ref<Record<string, string[]>>({})
// Un echec sans detail de validation (reseau, 419, 500) laissait le formulaire
// immobile et muet : on le dit.
const failed = ref(false)
const pending = ref(false)

useHead({ title: () => t('Import File') })

function onFileChange(event: Event) {
  file.value = (event.target as HTMLInputElement).files?.[0] ?? null
}

async function submit() {
  if (!file.value) return
  pending.value = true
  errors.value = {}
  failed.value = false
  const body = new FormData()
  body.append('file', file.value)
  try {
    const recipe = await apiSend<Recipe>('/api/recipes/import', 'POST', body)
    await navigateTo(`/recipes/${recipe.id}`)
  } catch (e) {
    errors.value = extractErrors(e)
    failed.value = Object.keys(errors.value).length === 0
  } finally {
    pending.value = false
  }
}
</script>

<template>
  <div class="import-page">
    <PageHeader :title="$t('Import File')" />
    <p v-if="failed" class="banner-error">{{ $t('Action failed') }}</p>
    <form id="import-form" class="panel import-page__form" @submit.prevent="submit">
      <label class="import-page__dropzone">
        <span v-if="!file" class="import-page__dropzone-action">{{ $t('Upload a file') }}</span>
        <span v-else class="import-page__filename">{{ file.name }}</span>
        <span class="import-page__hint">{{ $t('File BeerXML Format Only') }} - 2 Mo max</span>
        <input type="file" accept=".xml,text/xml" class="import-page__file-input" @change="onFileChange" />
      </label>
      <p v-if="errors.file" class="field__error">{{ errors.file[0] }}</p>
    </form>

    <!-- Un bouton grise sans raison est un cul-de-sac (contrat #6). -->
    <PageAction :reason="file ? null : $t('Choose a file first')">
      <template #cancel>
        <NuxtLink to="/recipes" class="btn btn-secondary">{{ $t('Cancel') }}</NuxtLink>
      </template>
      <button type="submit" form="import-form" class="btn" :class="{ 'btn-primary': file }" :disabled="pending || !file">
        {{ $t('Import the recipe') }}
      </button>
    </PageAction>
  </div>
</template>

<style scoped>
.import-page {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.import-page__form {
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.import-page__hint {
  display: block;
  margin-top: 0.375rem;
  font-size: var(--text-label);
  color: var(--color-text-faint);
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

/* La zone est un choix de fichier : elle s'annonce comme une action, pas comme
   une phrase descriptive perdue dans un rectangle. */
.import-page__dropzone-action {
  font-family: var(--font-display);
  text-transform: uppercase;
  letter-spacing: -0.02em;
  font-weight: 700;
  color: var(--color-text);
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

</style>
