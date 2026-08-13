<script setup lang="ts">
import type { Brewing, BrewingStep, Recipe, StepType } from '~/types/api'
import { COMPLETED_TABLES, type RecipeTableDescriptor, formatStepTime, timeUnitFor } from '~/utils/steps'

const { t } = useI18n()

const route = useRoute()
const recipeId = Number(route.params.recipe)
const brewingId = Number(route.params.brewing)

const { data: brewing, error: brewingError } = await useAsyncData(
  `brewing-${brewingId}`,
  () => apiGet<Brewing>(`/api/brewings/${brewingId}`),
  { deep: true },
)
const { data: recipe, error: recipeError } = await useAsyncData(`recipe-${recipeId}`, () => apiGet<Recipe>(`/api/recipes/${recipeId}`))

const fetchError = brewingError.value ?? recipeError.value
if (fetchError) {
  throw createError({ statusCode: fetchError.statusCode ?? 500, statusMessage: fetchError.statusMessage ?? fetchError.message })
}
if (brewing.value && brewing.value.recipe_id !== recipeId) {
  throw createError({ statusCode: 404 })
}

function brewingSteps(types: StepType[]) {
  return (brewing.value?.steps ?? []).filter((s) => types.includes(s.type))
}

// Parite web assumee : comparaison par position dans le meme groupe de types
// (les brewing_steps sont crees dans l'ordre des steps de la recette).
function recipeTimeAt(types: StepType[], index: number): number | null {
  const steps = (recipe.value?.steps ?? []).filter((s) => types.includes(s.type))
  return steps[index]?.time ?? null
}

// Pas d'en-tete de tableau partage avec DataRow : chaque ligne porte elle-meme
// l'unite de temps (toujours affichee, contrairement a l'ancien <thead>). Cle
// pluralisee (accord correct : "1 minute", pas "1 Minutes").
function stepMeta(table: RecipeTableDescriptor, step: BrewingStep): string | null {
  if (step.time == null) return null
  const unit = timeUnitFor(table, step.type)
  const amount = Number(formatStepTime(step.time, unit))
  return t(unit === 'days' ? 'n_days' : 'n_minutes', amount)
}

async function destroyBrewing() {
  if (!confirm(t('Are you sure?'))) return
  await apiSend(`/api/brewings/${brewingId}`, 'DELETE')
  await navigateTo(`/recipes/${recipeId}/brewings`)
}
</script>

<template>
  <div v-if="brewing" class="completed-page">
    <NuxtLink :to="`/recipes/${recipeId}/brewings`" class="completed-page__back">&larr; {{ $t('Brewing list') }}</NuxtLink>

    <div class="panel completed-page__card">
      <h1 class="completed-page__name">{{ brewing.name }} <span class="chip chip--done">{{ $t('finished') }}</span></h1>
      <div class="completed-page__dates">
        <p><span class="completed-page__label">{{ $t('Boil') }} :</span> <span class="data">{{ formatDate(brewing.boil_start) }}</span></p>
        <p><span class="completed-page__label">{{ $t('Ferment') }} :</span> <span class="data">{{ formatDate(brewing.ferment_start) }}</span></p>
        <p><span class="completed-page__label">{{ $t('End') }} :</span> <span class="data">{{ formatDate(brewing.ferment_end) }}</span></p>
      </div>
    </div>

    <section v-for="table in COMPLETED_TABLES" :key="table.title" class="completed-page__section">
      <template v-if="brewingSteps(table.types).length > 0">
        <h2 class="completed-page__heading">{{ $t(table.title) }}</h2>
        <div class="completed-page__rows">
          <DataRow
            v-for="(step, index) in brewingSteps(table.types)"
            :key="step.id"
            :label="step.field"
            :quantity="formatQuantity(step.quantity, step.unit)"
            :meta="stepMeta(table, step)"
            :checkable="false"
            :state="step.time !== recipeTimeAt(table.types, index) ? 'late' : 'pending'"
          />
        </div>
      </template>
    </section>

    <div class="panel completed-page__note">
      <h2 class="completed-page__heading">{{ $t('Note') }}</h2>
      <p class="completed-page__note-text">{{ brewing.note || '-' }}</p>
    </div>

    <button type="button" class="btn btn-danger" @click="destroyBrewing">{{ $t('delete this brewing') }}</button>
  </div>
</template>

<style scoped>
.completed-page {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.completed-page__back {
  align-self: flex-start;
  font-size: var(--text-label);
  color: var(--color-text-dim);
}

.completed-page__back:hover {
  color: var(--color-text);
}

.completed-page__card {
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.completed-page__name {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-family: var(--font-display);
  text-transform: uppercase;
  letter-spacing: -0.02em;
  font-weight: 800;
  font-size: 1.5rem;
}

.completed-page__dates {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  font-size: var(--text-label);
}

.completed-page__label {
  color: var(--color-text-dim);
}

.completed-page__section {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.completed-page__heading {
  font-family: var(--font-display);
  text-transform: uppercase;
  letter-spacing: -0.02em;
  font-weight: 700;
  font-size: 1.125rem;
}

.completed-page__rows {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.completed-page__note {
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.completed-page__note-text {
  white-space: pre-wrap;
  color: var(--color-text-dim);
  font-size: var(--text-body);
}

.chip--done {
  color: var(--color-ink);
  background-color: var(--color-malt);
}
</style>
