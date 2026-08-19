<script setup lang="ts">
import type { Brewing, Recipe, Step, StepType } from '~/types/api'
import { RECIPE_TABLES, type RecipeTableDescriptor, formatStepTime, timeUnitFor } from '~/utils/steps'

const { t } = useI18n()

const route = useRoute()
const recipeId = Number(route.params.recipe)
const { data: recipe, error, refresh } = await useAsyncData(`recipe-${recipeId}`, () => apiGet<Recipe>(`/api/recipes/${recipeId}`))
if (error.value) {
  throw createError({ statusCode: statusOf(error.value) })
}

/*
 * Les brassins, pour leurs notes. Meme cle que la liste des brassins, et memes
 * options : les deux ecrans partagent le meme chargement, et une note modifiee ici
 * est deja a jour la-bas.
 *
 * L'erreur n'est pas fatale, contrairement a celle de la recette : une fiche recette
 * reste lisible sans son journal, on le dit sur une ligne plutot que de remplacer la
 * page par un ecran d'erreur.
 */
const { data: brewings, error: brewingsError } = await useAsyncData(`brewings-${recipeId}`, () =>
  apiGet<Brewing[]>(`/api/recipes/${recipeId}/brewings`))

// La liste est shallow (defaut de Nuxt 4) : on remplace le tableau, on ne modifie
// pas la note dans l'objet, sinon l'ecran ne bougerait pas.
function applyNote({ id, note }: { id: number; note: string }) {
  brewings.value = (brewings.value ?? []).map((b) => (b.id === id ? { ...b, note } : b))
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

useHead({ title: () => recipe.value?.name ?? '' })

const showMore = ref(false)
const editingDensity = ref(false)
const densityInput = ref<string>('')
const densityError = ref(false)
const deleteError = ref(false)
const confirmOpen = ref(false)
const deleting = ref(false)

function startEditDensity() {
  densityInput.value = recipe.value?.before_boil_density?.toString() ?? ''
  editingDensity.value = true
}

/*
 * `densityInput.value.trim()` etait appele hors du try, sur une valeur que v-model
 * transformait en nombre des la premiere frappe (input de type number) : le
 * TypeError s'echappait, le bouton ne faisait rien et rien ne s'affichait. Le champ
 * est passe en texte (la virgule francaise y est acceptee) et la saisie passe par
 * parseDecimalInput, qui distingue « champ vide » de « pas un nombre ».
 */
async function saveDensity() {
  const parsed = parseDecimalInput(densityInput.value)
  if (parsed === undefined) {
    densityError.value = true
    return
  }
  try {
    await apiSend(`/api/recipes/${recipeId}`, 'PATCH', { before_boil_density: parsed })
    editingDensity.value = false
    densityError.value = false
    await refresh()
  } catch {
    densityError.value = true
  }
}

async function destroyRecipe() {
  deleting.value = true
  try {
    await apiSend(`/api/recipes/${recipeId}`, 'DELETE')
    await navigateTo('/recipes')
  } catch {
    deleteError.value = true
    confirmOpen.value = false
  } finally {
    deleting.value = false
  }
}
</script>

<template>
  <div v-if="recipe" class="recipe-detail">
    <PageHeader :title="recipe.name">
      <template #meta>
        <p class="recipe-detail__meta">
          <span>{{ recipe.type }}</span>
          <span class="recipe-detail__sep">-</span>
          <span class="data">{{ formatPercent(recipe.alcohol) }}</span>
          <span class="recipe-detail__sep">-</span>
          <span class="data">{{ formatQuantity(recipe.volume, 'l') }}</span>
        </p>
      </template>
      <template #action>
        <NuxtLink :to="`/recipes/${recipe.id}/brewings`" class="btn btn-secondary">
          {{ $t('Brewing list') }}
        </NuxtLink>
      </template>
    </PageHeader>

    <div class="panel recipe-detail__card" :style="{ '--recipe-color': ebcToHex(recipe.color) }">
      <!-- « 2 min » etait la seule pastille sans etiquette, au milieu de pastilles
           etiquetees : on ne savait pas de quelle duree il s'agissait. -->
      <div class="recipe-detail__chips">
        <span class="chip">{{ $t('Method') }} : {{ recipe.method }}</span>
        <span class="chip">IBU {{ formatNumber(recipe.bitterness) }}</span>
        <span class="chip">EBC {{ formatNumber(recipe.color) }}</span>
        <span class="chip">{{ $t('Boil time') }} : {{ formatNumber(recipe.boil_time) }} min</span>
      </div>

      <div class="recipe-detail__density">
        <span class="recipe-detail__density-label">{{ $t('Before Boil Density') }} :</span>
        <template v-if="!editingDensity">
          <!-- Une densite absente s'affichait en pastille pointillee contenant un
               tiret, ce qui se lisait comme un composant casse. On le dit. -->
          <span v-if="recipe.before_boil_density != null" class="data chip">
            {{ formatDensity(recipe.before_boil_density) }}
          </span>
          <span v-else class="recipe-detail__density-missing">{{ $t('Not set') }}</span>
          <button type="button" class="recipe-detail__link" @click="startEditDensity">{{ $t('Edit') }}</button>
        </template>
        <template v-else>
          <input
            v-model="densityInput"
            type="text"
            inputmode="decimal"
            :aria-label="$t('Before Boil Density')"
            class="recipe-detail__density-input data"
            @keyup.enter="saveDensity"
          />
          <button type="button" class="recipe-detail__link" @click="saveDensity">{{ $t('Save') }}</button>
          <button type="button" class="recipe-detail__link" @click="editingDensity = false">{{ $t('Cancel') }}</button>
        </template>
      </div>
      <p v-if="densityError" class="banner-error recipe-detail__density-error">{{ $t('Density must be a number') }}</p>

      <button
        type="button"
        class="recipe-detail__link recipe-detail__more"
        :aria-expanded="showMore"
        @click="showMore = !showMore"
      >
        {{ showMore ? $t('Less') : $t('More') }}
      </button>
      <!-- Les valeurs sortaient sans unite : « Efficacite 72.0 », « Carbonation 2.4 ». -->
      <dl v-if="showMore" class="recipe-detail__dl">
        <div class="recipe-detail__dl-item"><dt>{{ $t('Initial Density') }}</dt><dd class="data">{{ formatDensity(recipe.initial_density) }}</dd></div>
        <div class="recipe-detail__dl-item"><dt>{{ $t('Final Density') }}</dt><dd class="data">{{ formatDensity(recipe.final_density) }}</dd></div>
        <div class="recipe-detail__dl-item"><dt>{{ $t('Efficiency') }}</dt><dd class="data">{{ formatPercent(recipe.efficiency) }}</dd></div>
        <div class="recipe-detail__dl-item"><dt>{{ $t('Carbonation') }}</dt><dd class="data">{{ formatQuantity(recipe.carbonation, 'g/l') }}</dd></div>
        <div class="recipe-detail__dl-item"><dt>{{ $t('Ferment') }}</dt><dd>{{ recipe.ferment }}</dd></div>
      </dl>
    </div>

    <section v-for="table in RECIPE_TABLES" :key="table.title" class="recipe-detail__section">
      <template v-if="stepsFor(table.types).length > 0">
        <h2 class="section-title">{{ $t(table.title) }}</h2>
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

    <p v-if="brewingsError" class="banner-error">{{ $t('Notes not loaded') }}</p>
    <BrewingNotes :brewings="brewings" @saved="applyNote" />

    <!-- Action destructive en bas de page (P2) : une fiche recette est un ecran de
         consultation, la suppression ne doit pas etre la premiere chose que l'oeil
         attrape. Meme traitement que la suppression d'un brassin : elle avait la
         meme gravite mais deux apparences differentes selon l'ecran. -->
    <div class="rule recipe-detail__separator" />
    <button type="button" class="btn btn-danger recipe-detail__delete" @click="confirmOpen = true">
      {{ $t('Delete recipe') }}
    </button>
    <p v-if="deleteError" class="banner-error">{{ $t('Recipe not deleted') }}</p>

    <PageAction>
      <NuxtLink :to="`/recipes/${recipe.id}/brewings/new`" class="btn btn-primary">
        <AppIcon name="plus" />
        <span>{{ $t('Brew this recipe') }}</span>
      </NuxtLink>
    </PageAction>

    <ConfirmDialog
      :open="confirmOpen"
      :title="$t('Delete this recipe?')"
      :detail="`${$t('Its brewings will be deleted too.')} ${$t('Deleting is final.')}`"
      :confirm-label="$t('Delete')"
      :pending="deleting"
      @confirm="destroyRecipe"
      @close="confirmOpen = false"
    />
  </div>
</template>

<style scoped>
.recipe-detail {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.recipe-detail__card {
  padding: 1.25rem;
  border-left: 4px solid var(--recipe-color, var(--color-rule));
}

.recipe-detail__meta {
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
  flex-wrap: wrap;
  align-items: center;
  gap: 0.5rem;
  font-size: var(--text-label);
}

.recipe-detail__density-label {
  color: var(--color-text-dim);
}

.recipe-detail__density-missing {
  color: var(--color-text-faint);
}

.recipe-detail__density-input {
  min-height: 48px;
  width: 7rem;
  background-color: var(--color-panel);
  border: 1px solid var(--color-rule);
  border-radius: var(--radius);
  padding-inline: 0.5rem;
  color: var(--color-text);
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

.recipe-detail__rows {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}


.recipe-detail__separator {
  margin-top: 1rem;
  border-top: 1px solid var(--color-rule);
}

.recipe-detail__delete {
  align-self: flex-start;
}
</style>
