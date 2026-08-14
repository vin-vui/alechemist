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
  throw createError({ statusCode: statusOf(fetchError) })
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

useHead({ title: () => brewing.value?.name ?? '' })

// Pas d'en-tete de tableau partage avec DataRow : chaque ligne porte elle-meme
// l'unite de temps (toujours affichee, contrairement a l'ancien <thead>). Cle
// pluralisee (accord correct : "1 minute", pas "1 Minutes").
//
// L'ecart avec la recette est dit en toutes lettres (« 20 jours - prevu 14 jours »)
// au lieu d'etre code par le seul filet ember, qui se lisait « en retard » sur un
// brassin pourtant termine. « Rien ne depend de la seule couleur » (contrat).
function stepMeta(table: RecipeTableDescriptor, step: BrewingStep, index: number): string | null {
  if (step.time == null) return null
  const unit = timeUnitFor(table, step.type)
  const key = unit === 'days' ? 'n_days' : 'n_minutes'
  const actual = t(key, Number(formatStepTime(step.time, unit)))

  const planned = recipeTimeAt(table.types, index)
  if (planned == null || planned === step.time) return actual

  return `${actual} - ${t('planned')} ${t(key, Number(formatStepTime(planned, unit)))}`
}

// La suppression ne vit plus ici : elle est sur les cartes des listes de brassins,
// seul endroit d'ou l'on supprime, jamais au milieu d'un ecran de consultation.

const noteOpen = ref(false)
</script>

<template>
  <div v-if="brewing" class="completed-page">
    <PageHeader :title="brewing.name">
      <template #meta>
        <p class="completed-page__recipe">
          <span class="chip chip--done">{{ $t('finished') }}</span>
          <!-- La recette est nommee en lien : sans retour, c'est le seul chemin
               vers la fiche de ce qu'on vient de brasser. -->
          <NuxtLink v-if="recipe" :to="`/recipes/${recipeId}`" class="completed-page__recipe-link">
            {{ recipe.name }}
          </NuxtLink>
        </p>
      </template>
    </PageHeader>

    <div class="panel completed-page__card">
      <!-- Une date absente laissait un libelle suivi de rien (« Ébullition : »). -->
      <div class="completed-page__dates">
        <p v-if="brewing.boil_start">
          <span class="completed-page__label">{{ $t('Boil') }} :</span> <span class="data">{{ formatDate(brewing.boil_start) }}</span>
        </p>
        <p v-if="brewing.ferment_start">
          <span class="completed-page__label">{{ $t('Ferment') }} :</span> <span class="data">{{ formatDate(brewing.ferment_start) }}</span>
        </p>
        <p v-if="brewing.ferment_end">
          <span class="completed-page__label">{{ $t('End') }} :</span> <span class="data">{{ formatDate(brewing.ferment_end) }}</span>
        </p>
      </div>
    </div>

    <section v-for="table in COMPLETED_TABLES" :key="table.title" class="completed-page__section">
      <template v-if="brewingSteps(table.types).length > 0">
        <h2 class="section-title">{{ $t(table.title) }}</h2>
        <div class="completed-page__rows">
          <DataRow
            v-for="(step, index) in brewingSteps(table.types)"
            :key="step.id"
            :label="step.field"
            :quantity="formatQuantity(step.quantity, step.unit)"
            :meta="stepMeta(table, step, index)"
            :checkable="false"
          />
        </div>
      </template>
    </section>

    <div class="panel completed-page__note">
      <h2 class="section-title">{{ $t('Note') }}</h2>
      <p class="completed-page__note-text">{{ brewing.note || $t('No note yet') }}</p>
      <!-- La note s'ecrit dans un dialogue pose sur ce recapitulatif : elle ne fait
           plus quitter l'ecran pour y revenir ensuite. -->
      <button type="button" class="completed-page__note-link" @click="noteOpen = true">
        {{ brewing.note ? $t('Edit the note') : $t('Add a note') }} &rarr;
      </button>
    </div>

    <NoteDialog
      :brewing="brewing"
      :open="noteOpen"
      @close="noteOpen = false"
      @saved="brewing.note = $event"
    />
    <!-- Un brassin termine n'offrait que sa suppression : c'est le moment ou l'on
         veut refaire la meme biere, pas effacer sa trace. -->
    <PageAction>
      <NuxtLink :to="`/recipes/${recipeId}/brewings/new`" class="btn btn-primary">
        <AppIcon name="repeat" />
        <span>{{ $t('Brew again') }}</span>
      </NuxtLink>
    </PageAction>
  </div>
</template>

<style scoped>
.completed-page {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.completed-page__card {
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}


.completed-page__recipe {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.5rem;
  color: var(--color-text-dim);
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

.completed-page__note-link {
  align-self: flex-start;
  min-height: 44px;
  display: inline-flex;
  align-items: center;
  font-size: var(--text-label);
  color: var(--color-signal);
}

.completed-page__recipe-link {
  text-decoration: underline;
  text-underline-offset: 2px;
}

.completed-page__recipe-link:hover {
  color: var(--color-text);
}



</style>
