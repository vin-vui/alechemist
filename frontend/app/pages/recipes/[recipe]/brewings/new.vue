<script setup lang="ts">
import type { Brewing, Recipe } from '~/types/api'

const { t } = useI18n()
const route = useRoute()
const recipeId = Number(route.params.recipe)

// L'ecran ne disait pas de quelle recette il s'agissait : arrive par un lien
// profond, on remplissait un formulaire sans savoir ce qu'on allait brasser.
const { data: recipe } = await useAsyncData(`recipe-${recipeId}`, () => apiGet<Recipe>(`/api/recipes/${recipeId}`))

const form = reactive({
  // Nom pre-rempli : un champ obligatoire et vide, sans exemple, laissait deviner
  // ce qu'on attendait.
  name: recipe.value ? `${recipe.value.name} - ${formatDate(new Date().toISOString())}` : '',
  date_start: new Date().toISOString().slice(0, 10),
  note: '',
})
const errors = ref<Record<string, string[]>>({})
// Un echec sans detail de validation (reseau, 419, 500) laissait le formulaire
// immobile et muet : on le dit.
const failure = ref<string | null>(null)
const pending = ref(false)

useHead({ title: () => t('New Brewing') })

async function submit() {
  errors.value = {}
  failure.value = null
  pending.value = true
  try {
    const brewing = await apiSend<Brewing>(`/api/recipes/${recipeId}/brewings`, 'POST', { ...form })
    await navigateTo(`/recipes/${recipeId}/brewings/${brewing.id}/preparation`)
  } catch (e) {
    errors.value = extractErrors(e)
    failure.value = Object.keys(errors.value).length === 0 ? failureMessageKey(e) : null
  } finally {
    pending.value = false
  }
}
</script>

<template>
  <div class="new-brewing">
    <PageHeader :title="$t('New Brewing')">
      <template #meta>
        <!-- L'ecran ne disait pas de quelle recette il s'agissait : arrive par un
             lien profond, on remplissait un formulaire sans savoir quoi brasser. -->
        <p v-if="recipe" class="new-brewing__recipe">{{ recipe.name }}</p>
      </template>
    </PageHeader>
    <p v-if="failure" class="banner-error">{{ $t(failure) }}</p>
    <form id="new-brewing-form" class="panel new-brewing__form" @submit.prevent="submit">
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
        <textarea
          id="note"
          v-model="form.note"
          rows="6"
          class="field__input field__textarea"
          :placeholder="$t('Note placeholder')"
        />
      </div>
    </form>

    <!-- Un formulaire se quitte par son annulation : c'est la seule sortie de cet
         ecran, il n'y a plus de lien de retour. -->
    <PageAction>
      <template #cancel>
        <NuxtLink :to="`/recipes/${recipeId}`" class="btn btn-secondary">{{ $t('Cancel') }}</NuxtLink>
      </template>
      <button type="submit" form="new-brewing-form" class="btn btn-primary" :disabled="pending">
        {{ $t('Save & Start') }}
      </button>
    </PageAction>
  </div>
</template>

<style scoped>
.new-brewing {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.new-brewing__recipe {
  margin-top: 0.25rem;
  color: var(--color-text-dim);
}

.new-brewing__form {
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

</style>
