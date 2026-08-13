<script setup lang="ts">
import type { Brewing, BrewingStep } from '~/types/api'
import { STEP_CONFIG, isStepSlug, stepsForPage, type StepSlug } from '~/utils/steps'

definePageMeta({
  key: (route) => route.fullPath,
  validate: (route) => isStepSlug(route.params.step),
})

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

async function toggle(step: BrewingStep) {
  if (!isCurrent.value) return
  const updated = await apiSend<BrewingStep>(`/api/brewing-steps/${step.id}`, 'PATCH', { status: !step.status })
  step.status = updated.status
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
</script>

<template>
  <div v-if="brewing" class="space-y-4">
    <StepHeader :brewing="brewing" :title="config.title" />
    <StepIndicator :brewing="brewing" :active="stepKey" />

    <p v-if="!isCurrent" class="rounded bg-gray-100 p-2 text-sm text-gray-600">
      {{ $t('Current step') }} : {{ $t(brewing.current_step ?? 'preparation') }}
    </p>

    <!-- Etapes ferment : echeancier -->
    <div v-if="stepKey === 'ferment' && brewing.ferment_start" class="space-y-2">
      <div
        v-for="view in fermentSchedule(brewing.ferment_start, brewing.steps ?? [], now)"
        :key="view.step.id"
        class="flex items-center gap-3 rounded border-l-4 bg-white p-3 shadow"
        :class="view.late ? 'border-red-600' : view.step.status ? 'border-old-gold' : 'border-gray-200'"
      >
        <input
          type="checkbox"
          :checked="view.step.status"
          :disabled="!isCurrent"
          class="size-5 rounded text-xanthous"
          @change="toggle(view.step)"
        />
        <div class="flex-1">
          <p>{{ view.step.field }}</p>
          <p class="text-sm text-gray-500">
            {{ $t('n_days', view.durationDays) }} -
            {{ view.late ? $t('late') : `${$t('n_days', view.daysLeft)} ${$t('left')}` }} -
            {{ formatDate(view.endsAt.toISOString()) }}
          </p>
        </div>
      </div>
    </div>

    <!-- Etape boil : chrono client (Tache 17) -->
    <BoilPanel
      v-else-if="stepKey === 'boil'"
      :brewing="brewing"
      :steps="pageSteps"
      :readonly="!isCurrent"
      @refresh="refresh"
      @toggle="toggle"
      @done="boilDone = $event"
    />

    <!-- Autres etapes : liste simple -->
    <div v-else class="space-y-2">
      <div
        v-for="step in pageSteps"
        :key="step.id"
        class="flex items-center gap-3 rounded border-l-4 bg-white p-3 shadow"
        :class="step.status ? 'border-old-gold' : 'border-gray-200'"
      >
        <input
          type="checkbox"
          :checked="step.status"
          :disabled="!isCurrent"
          class="size-5 rounded text-xanthous"
          @change="toggle(step)"
        />
        <div class="flex-1">
          <p v-if="stepKey === 'preparation' && step.unit === 'l'">
            {{ $t('Heat') }} {{ step.quantity }} l {{ $t('at') }} {{ step.field }}
          </p>
          <p v-else-if="stepKey === 'mash'">
            {{ step.field }} {{ $t('at') }} {{ formatQuantity(step.quantity, step.unit) }}
            <span v-if="step.time"> {{ $t('for') }} {{ $t('n_minutes', step.time) }}</span>
          </p>
          <p v-else>
            {{ formatQuantity(step.quantity, step.unit) }} - {{ step.field }}
          </p>
        </div>
      </div>
    </div>

    <NextButton :enabled="canGoNext" :end-of-brewing="endOfBrewing" @next="next" />
  </div>
</template>
