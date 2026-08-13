<script setup lang="ts">
import type { Recipe, Step, StepType } from '~/types/api'
import { RECIPE_TABLES, type RecipeTableDescriptor, formatStepTime, timeUnitFor } from '~/utils/steps'

const { t } = useI18n()

const route = useRoute()
const recipeId = Number(route.params.recipe)
const { data: recipe, error, refresh } = await useAsyncData(`recipe-${recipeId}`, () => apiGet<Recipe>(`/api/recipes/${recipeId}`))
if (error.value) {
  throw createError({ statusCode: error.value.statusCode ?? 500, statusMessage: error.value.statusMessage ?? error.value.message })
}

function stepsFor(types: StepType[]): Step[] {
  return (recipe.value?.steps ?? []).filter((s) => types.includes(s.type))
}

// Pas d'en-tete de tableau partage avec DataRow (contrairement a l'ancien <thead>) :
// chaque ligne doit donc porter elle-meme l'unite de temps, toujours affichee (et
// plus seulement quand elle differe de l'unite par defaut de la table). Cle pluralisee
// (n_minutes/n_days, deja utilisee par [step].vue) plutot que le libelle de colonne
// Minutes/Days fixe, pour un accord correct ("1 minute", pas "1 Minutes").
function stepTimeMeta(table: RecipeTableDescriptor, step: Step): string | null {
  if (step.time == null) return null
  const unit = timeUnitFor(table, step.type)
  const amount = Number(formatStepTime(step.time, unit))
  return t(unit === 'days' ? 'n_days' : 'n_minutes', amount)
}

const showMore = ref(false)
const editingDensity = ref(false)
const densityInput = ref<string>('')
const densityError = ref(false)
const deleteError = ref(false)

function startEditDensity() {
  densityInput.value = recipe.value?.before_boil_density?.toString() ?? ''
  editingDensity.value = true
}

async function saveDensity() {
  const trimmed = densityInput.value.trim()
  try {
    await apiSend(`/api/recipes/${recipeId}`, 'PATCH', { before_boil_density: trimmed === '' ? null : Number(trimmed) })
    editingDensity.value = false
    densityError.value = false
    await refresh()
  } catch {
    densityError.value = true
  }
}

async function destroyRecipe() {
  if (!confirm(t('Are you sure?'))) return
  try {
    await apiSend(`/api/recipes/${recipeId}`, 'DELETE')
    await navigateTo('/recipes')
  } catch {
    deleteError.value = true
  }
}
</script>

<template>
  <div v-if="recipe" class="recipe-detail">
    <div class="recipe-detail__topbar">
      <NuxtLink to="/recipes" class="recipe-detail__back">&larr; {{ $t('Recipes') }}</NuxtLink>
      <button type="button" class="btn btn-danger" @click="destroyRecipe">{{ $t('Delete recipe') }}</button>
    </div>

    <p v-if="deleteError" class="banner-error">{{ $t('Recipe not deleted') }}</p>

    <div class="panel recipe-detail__card" :style="{ '--recipe-color': ebcToHex(recipe.color) }">
      <h1 class="recipe-detail__name">{{ recipe.name }}</h1>
      <p class="recipe-detail__meta">
        <span>{{ recipe.type }}</span>
        <span class="recipe-detail__sep">-</span>
        <span class="data">{{ recipe.alcohol }} %</span>
        <span class="recipe-detail__sep">-</span>
        <span class="data">{{ recipe.volume }} l</span>
      </p>

      <div class="recipe-detail__chips">
        <span class="chip">{{ $t('Method') }} : {{ recipe.method }}</span>
        <span class="chip">IBU {{ recipe.bitterness }}</span>
        <span class="chip">EBC {{ recipe.color }}</span>
        <span class="chip">{{ recipe.boil_time }} min</span>
      </div>

      <div class="recipe-detail__density">
        <span class="recipe-detail__density-label">{{ $t('Before Boil Density') }} :</span>
        <template v-if="!editingDensity">
          <span class="data chip" :class="{ 'recipe-detail__density-value--missing': recipe.before_boil_density == null }">
            {{ recipe.before_boil_density ?? '-' }}
          </span>
          <button type="button" class="recipe-detail__link" @click="startEditDensity">{{ $t('Edit') }}</button>
        </template>
        <template v-else>
          <input v-model="densityInput" type="number" step="0.001" class="recipe-detail__density-input" />
          <button type="button" class="recipe-detail__link" @click="saveDensity">{{ $t('Save') }}</button>
        </template>
      </div>
      <p v-if="densityError" class="banner-error recipe-detail__density-error">{{ $t('Density not saved') }}</p>

      <button type="button" class="recipe-detail__link recipe-detail__more" @click="showMore = !showMore">
        {{ showMore ? $t('Less') : $t('More') }}
      </button>
      <dl v-if="showMore" class="recipe-detail__dl">
        <div class="recipe-detail__dl-item"><dt>{{ $t('Initial Density') }}</dt><dd class="data">{{ recipe.initial_density }}</dd></div>
        <div class="recipe-detail__dl-item"><dt>{{ $t('Final Density') }}</dt><dd class="data">{{ recipe.final_density }}</dd></div>
        <div class="recipe-detail__dl-item"><dt>{{ $t('Efficiency') }}</dt><dd>{{ recipe.efficiency }}</dd></div>
        <div class="recipe-detail__dl-item"><dt>{{ $t('Carbonation') }}</dt><dd class="data">{{ recipe.carbonation }}</dd></div>
        <div class="recipe-detail__dl-item"><dt>{{ $t('Ferment') }}</dt><dd>{{ recipe.ferment }}</dd></div>
      </dl>
    </div>

    <section v-for="table in RECIPE_TABLES" :key="table.title" class="recipe-detail__section">
      <template v-if="stepsFor(table.types).length > 0">
        <h2 class="recipe-detail__heading">{{ $t(table.title) }}</h2>
        <div class="recipe-detail__rows">
          <DataRow
            v-for="step in stepsFor(table.types)"
            :key="step.id"
            :label="step.field"
            :quantity="formatQuantity(step.quantity, step.unit)"
            :meta="stepTimeMeta(table, step)"
            :checkable="false"
          />
        </div>
      </template>
    </section>

    <NuxtLink :to="`/recipes/${recipe.id}/brewings`" class="btn btn-primary recipe-detail__brewings">
      {{ $t('Brewing list') }}
    </NuxtLink>
  </div>
</template>

<style scoped>
.recipe-detail {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.recipe-detail__topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
}

.recipe-detail__back {
  font-size: var(--text-label);
  color: var(--color-text-dim);
}

.recipe-detail__back:hover {
  color: var(--color-text);
}

.recipe-detail__card {
  padding: 1.25rem;
  border-left: 4px solid var(--recipe-color, var(--color-rule));
}

.recipe-detail__name {
  font-family: var(--font-display);
  text-transform: uppercase;
  letter-spacing: -0.02em;
  font-weight: 800;
  font-size: 1.5rem;
}

.recipe-detail__meta {
  margin-top: 0.25rem;
  font-size: var(--text-label);
  color: var(--color-text-dim);
}

.recipe-detail__sep {
  margin-inline: 0.375rem;
  color: var(--color-text-faint);
}

.recipe-detail__chips {
  margin-top: 0.75rem;
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.recipe-detail__density {
  margin-top: 0.75rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: var(--text-label);
}

.recipe-detail__density-label {
  color: var(--color-text-dim);
}

.recipe-detail__density-value--missing {
  color: var(--color-ember);
  background-color: transparent;
  border: 1px dashed var(--color-ember);
}

.recipe-detail__density-input {
  height: 48px;
  width: 6rem;
  background-color: var(--color-panel);
  border: 1px solid var(--color-rule);
  border-radius: var(--radius);
  padding-inline: 0.5rem;
  color: var(--color-text);
  font-family: var(--font-mono);
}

.recipe-detail__density-error {
  margin-top: 0.5rem;
}

.recipe-detail__link {
  font-size: var(--text-label);
  color: var(--color-text-dim);
  text-decoration: underline;
}

.recipe-detail__link:hover {
  color: var(--color-text);
}

.recipe-detail__more {
  margin-top: 0.75rem;
  display: block;
}

.recipe-detail__dl {
  margin-top: 0.75rem;
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.75rem;
  padding-top: 0.75rem;
  border-top: 1px solid var(--color-rule);
}

@media (min-width: 640px) {
  .recipe-detail__dl {
    grid-template-columns: repeat(3, 1fr);
  }
}

.recipe-detail__dl-item dt {
  font-size: var(--text-label);
  color: var(--color-text-dim);
}

.recipe-detail__dl-item dd {
  margin-top: 0.125rem;
  color: var(--color-text);
}

.recipe-detail__section {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.recipe-detail__heading {
  font-family: var(--font-display);
  text-transform: uppercase;
  letter-spacing: -0.02em;
  font-weight: 700;
  font-size: 1.125rem;
}

.recipe-detail__rows {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.recipe-detail__brewings {
  align-self: flex-start;
}
</style>
