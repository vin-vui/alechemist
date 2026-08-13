<script setup lang="ts">
import type { Brewing, BrewingStep } from '~/types/api'
import type { FermentStepView } from '~/utils/ferment'
import { STEP_CONFIG, isStepSlug, stepsForPage, type StepSlug } from '~/utils/steps'

definePageMeta({
  key: (route) => route.fullPath,
  validate: (route) => isStepSlug(route.params.step),
})

const { t } = useI18n()

const route = useRoute()
const recipeId = Number(route.params.recipe)
const brewingId = Number(route.params.brewing)
const stepKey = route.params.step as StepSlug
const config = STEP_CONFIG[stepKey]

const { data: brewing, error, refresh } = await useAsyncData(
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

const isCurrent = computed(() => brewing.value?.current_step === stepKey)
const pageSteps = computed(() => stepsForPage(stepKey, brewing.value?.steps ?? []))
const allChecked = computed(() => pageSteps.value.length > 0 && pageSteps.value.every((s) => s.status))
const endOfBrewing = computed(() => brewing.value?.ferment_start != null)
const boilDone = ref(false)

// Gating par etape.
const canGoNext = computed(() => {
  if (!isCurrent.value || !allChecked.value) return false
  if (stepKey === 'boil') return boilDone.value
  if (stepKey === 'ferment') return brewing.value?.ferment_start != null
  return true
})

// Raison affichee quand le bouton est desactive (contrat #6) : jamais de cul-de-sac
// muet. Pas de raison hors etape courante, deja expliquee par le bandeau au-dessus.
const nextDisabledReason = computed(() => {
  if (canGoNext.value || !isCurrent.value) return null
  if (!allChecked.value) return t('Check all rows')
  if (stepKey === 'boil') return t('Timer not finished')
  if (stepKey === 'ferment') return t('Fermentation not started')
  return null
})

const mutationError = ref(false)

async function toggle(step: BrewingStep) {
  if (!isCurrent.value) return
  try {
    const updated = await apiSend<BrewingStep>(`/api/brewing-steps/${step.id}`, 'PATCH', { status: !step.status })
    step.status = updated.status
    mutationError.value = false
  } catch {
    // Le PATCH a echoue : la case cochee dans le template (:checked="view.step.status")
    // n'a pas ete persistee. On resynchronise depuis le serveur pour ne pas laisser
    // une case visuellement cochee sans effet, et on signale l'erreur.
    mutationError.value = true
    await refresh()
  }
}

async function next() {
  if (!canGoNext.value) return
  // Le PATCH ne renvoie pas `steps` (whenLoaded non charge cote API) : on fusionne
  // uniquement les champs modifies dans le cache partage (cle brewing-${brewingId},
  // meme cle que completed/note) plutot que de remplacer brewing.value entier, pour
  // ne pas perdre les etapes deja chargees. Sans cette fusion, la page suivante
  // (meme cle useAsyncData => pas de refetch) afficherait un current_step perime et
  // ne verrait pas ferment_start/ferment_end poses en effet de bord cote serveur.
  const updated = await apiSend<Brewing>(`/api/brewings/${brewingId}`, 'PATCH', { current_step: config.next })
  if (brewing.value) {
    brewing.value.current_step = updated.current_step
    brewing.value.ferment_start = updated.ferment_start
    brewing.value.ferment_end = updated.ferment_end
  }
  await navigateTo(`/recipes/${recipeId}/brewings/${brewingId}/${config.next}`)
}

const now = ref(new Date())

// --- Habillage DataRow des etapes hors boil/ferment (comportement inchange). ---
function rowState(step: BrewingStep): 'pending' | 'done' {
  return step.status ? 'done' : 'pending'
}

function otherRowLabel(step: BrewingStep): string {
  if (stepKey === 'preparation' && step.unit === 'l') return `${t('Heat')} ${t('at')} ${step.field}`
  return step.field
}

function otherRowQuantity(step: BrewingStep): string {
  if (stepKey === 'preparation' && step.unit === 'l') return `${step.quantity} l`
  return formatQuantity(step.quantity, step.unit)
}

function otherRowMeta(step: BrewingStep): string | null {
  if (stepKey === 'mash' && step.time) return `${t('for')} ${t('n_minutes', step.time)}`
  return null
}

// --- Habillage DataRow de l'echeancier ferment (comportement inchange). ---
function fermentRowMeta(view: FermentStepView): string {
  const duration = t('n_days', view.durationDays)
  const remaining = view.late ? t('late') : `${t('n_days', view.daysLeft)} ${t('left')}`
  return `${duration} - ${remaining} - ${formatDate(view.endsAt.toISOString())}`
}
</script>

<template>
  <div v-if="brewing" class="step-page">
    <StepHeader :brewing="brewing" :title="config.title" />
    <StepSpine :brewing="brewing" :active="stepKey" />

    <p v-if="!isCurrent" class="banner-info">
      {{ $t('Current step') }} : {{ $t(brewing.current_step ?? 'preparation') }}
    </p>

    <p v-if="mutationError" class="banner-error">
      {{ $t('Whoops! Something went wrong.') }}
    </p>

    <!-- Etape ferment : echeancier -->
    <div v-if="stepKey === 'ferment' && brewing.ferment_start" class="step-page__rows">
      <DataRow
        v-for="view in fermentSchedule(brewing.ferment_start, brewing.steps ?? [], now)"
        :key="view.step.id"
        :label="view.step.field"
        :meta="fermentRowMeta(view)"
        :state="view.late ? 'late' : view.step.status ? 'done' : 'pending'"
        :checked="view.step.status"
        :disabled="!isCurrent"
        @toggle="toggle(view.step)"
      />
    </div>

    <!-- Etape boil : chrono client + BoilTrack signature -->
    <BoilPanel
      v-else-if="stepKey === 'boil'"
      :brewing="brewing"
      :steps="pageSteps"
      :readonly="!isCurrent"
      @refresh="refresh"
      @toggle="toggle"
      @done="boilDone = $event"
    />

    <!-- Autres etapes : DataRow simples -->
    <div v-else class="step-page__rows">
      <DataRow
        v-for="step in pageSteps"
        :key="step.id"
        :label="otherRowLabel(step)"
        :quantity="otherRowQuantity(step)"
        :meta="otherRowMeta(step)"
        :state="rowState(step)"
        :checked="step.status"
        :disabled="!isCurrent"
        @toggle="toggle(step)"
      />
    </div>

    <NextButton :enabled="canGoNext" :end-of-brewing="endOfBrewing" :reason="nextDisabledReason" @next="next" />
  </div>
</template>

<style scoped>
.step-page {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.step-page__rows {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}
</style>
