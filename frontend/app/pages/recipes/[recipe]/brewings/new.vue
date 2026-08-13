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
  <div class="new-brewing">
    <h1 class="new-brewing__title">{{ $t('New Brewing') }}</h1>
    <form class="panel new-brewing__form" @submit.prevent="submit">
      <div class="field">
        <label class="field__label" for="name">{{ $t('Name') }}</label>
        <input id="name" v-model="form.name" required maxlength="255" class="field__input" />
        <p v-if="errors.name" class="field__error">{{ errors.name[0] }}</p>
      </div>
      <div class="field">
        <label class="field__label" for="date_start">{{ $t('Date') }}</label>
        <input id="date_start" v-model="form.date_start" type="date" required class="field__input data" />
        <p v-if="errors.date_start" class="field__error">{{ errors.date_start[0] }}</p>
      </div>
      <div class="field">
        <label class="field__label" for="note">{{ $t('Note') }}</label>
        <textarea id="note" v-model="form.note" rows="8" class="field__input field__textarea" />
      </div>
      <button type="submit" class="btn btn-primary new-brewing__submit">{{ $t('Save & Start') }}</button>
    </form>
  </div>
</template>

<style scoped>
.new-brewing {
  max-width: 28rem;
  margin-inline: auto;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.new-brewing__title {
  font-family: var(--font-display);
  text-transform: uppercase;
  letter-spacing: -0.02em;
  font-weight: 800;
  font-size: 1.5rem;
}

.new-brewing__form {
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
}

.field__label {
  font-size: var(--text-label);
  color: var(--color-text-dim);
}

.field__input {
  height: 48px;
  background-color: var(--color-panel);
  border: 1px solid var(--color-rule);
  border-radius: var(--radius);
  padding-inline: 0.875rem;
  color: var(--color-text);
  font-family: var(--font-sans);
}

.field__textarea {
  height: auto;
  padding-block: 0.625rem;
  font-family: var(--font-sans);
  resize: vertical;
}

.field__error {
  font-size: var(--text-label);
  color: var(--color-ember);
}

.new-brewing__submit {
  width: 100%;
}
</style>
