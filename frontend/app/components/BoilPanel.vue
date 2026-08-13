<script setup lang="ts">
// Ecran signature : Chrono + BoilTrack + DataRow (docs/design-system.md #2, #3, #4).
// Logique de chrono, de mutation et de gating inchangee ; seul l'habillage change.
import type { Brewing, BrewingStep } from '~/types/api'
import { boilRemainingMs, isStepLate, stepRemainingMinutes } from '~/utils/boil'
import { formatDuration } from '~/utils/format'

const props = defineProps<{ brewing: Brewing; steps: BrewingStep[]; readonly: boolean }>()
const emit = defineEmits<{ refresh: []; toggle: [step: BrewingStep]; done: [value: boolean] }>()

const { t } = useI18n()

const nowMs = ref(Date.now())
let timer: ReturnType<typeof setInterval> | undefined
onMounted(() => { timer = setInterval(() => { nowMs.value = Date.now() }, 1000) })
onUnmounted(() => clearInterval(timer))

const started = computed(() => props.brewing.boil_start !== null)
const remainingMs = computed(() => boilRemainingMs(props.brewing.boil_start, props.brewing.boil_time, nowMs.value))
const elapsed = computed(() => remainingMs.value !== null && remainingMs.value <= 0)

watchEffect(() => emit('done', started.value && elapsed.value))

// Le chrono affiche toujours le temps restant une fois demarre, la duree totale
// avant : les chiffres ne s'animent pas, seule la BoilTrack progresse visuellement.
const chronoDisplayMs = computed(() => (started.value ? Math.max(0, remainingMs.value ?? 0) : props.brewing.boil_time * 60_000))
const chronoLabel = computed(() => formatDuration(chronoDisplayMs.value))
const chronoUrgent = computed(() => started.value && !elapsed.value && (remainingMs.value ?? 0) < 60_000)

const modalOpen = ref(false)
const newBoilTime = ref(props.brewing.boil_time)
const mutationError = ref(false)

async function start() {
  try {
    await apiSend(`/api/brewings/${props.brewing.id}/start-boil`, 'POST')
    mutationError.value = false
    emit('refresh')
  } catch {
    mutationError.value = true
  }
}

async function saveBoilTime() {
  if (newBoilTime.value > 0) {
    try {
      await apiSend(`/api/brewings/${props.brewing.id}`, 'PATCH', { boil_time: Math.trunc(newBoilTime.value) })
      mutationError.value = false
      emit('refresh')
    } catch {
      mutationError.value = true
    }
  }
  modalOpen.value = false
}

function rowState(step: BrewingStep): 'pending' | 'done' | 'late' {
  if (step.status) return 'done'
  if (isStepLate(props.brewing.boil_start, props.brewing.boil_time, step, nowMs.value)) return 'late'
  return 'pending'
}

// Une ligne cochee est faite : plus d'echeance a afficher (D3), qu'il s'agisse
// d'un decompte ("dans 0 minutes") ou de la description statique des Aroma
// ("hors flamme pendant 0 minutes") -- les deux etaient fausses une fois la case
// cochee. DataRow masque la meta quand elle est vide/nulle (v-if="meta"), donc
// "rien" suffit : le filet gauche --color-malt et la coche disent deja "fait".
function rowMeta(step: BrewingStep): string | null {
  if (step.status) return null
  if (step.type === 'Aroma') {
    return `${t('flame-out')} ${t('for')} ${t('n_minutes', step.time ?? 0)}`
  }
  if (started.value) {
    if (isStepLate(props.brewing.boil_start, props.brewing.boil_time, step, nowMs.value)) {
      return t('late')
    }
    const remaining = stepRemainingMinutes(props.brewing.boil_start, props.brewing.boil_time, step, nowMs.value)
    return `${t('in')} ${t('n_minutes', Math.max(0, remaining ?? 0))}`
  }
  return `${t('n_minutes', step.time ?? 0)} ${t('before ends')}`
}
</script>

<template>
  <div class="boil-panel">
    <p v-if="mutationError" class="banner-error">{{ $t('Whoops! Something went wrong.') }}</p>

    <div class="boil-panel__chrono-block">
      <p
        class="boil-panel__chrono data"
        :class="{
          'boil-panel__chrono--signal': started && !elapsed && !chronoUrgent,
          'boil-panel__chrono--ember': chronoUrgent || (started && elapsed),
        }"
      >
        {{ chronoLabel }}
      </p>
      <p v-if="started" class="boil-panel__state">
        {{ elapsed ? $t('Time elapsed') : $t('in progress') }}
      </p>
      <div v-else-if="!readonly" class="boil-panel__actions">
        <button type="button" class="btn btn-primary" @click="start">{{ $t('Start') }}</button>
        <button
          type="button"
          class="btn btn-secondary"
          @click="modalOpen = true; newBoilTime = brewing.boil_time"
        >
          {{ $t('Change Time') }}
        </button>
      </div>
    </div>

    <BoilTrack :boil-start="brewing.boil_start" :boil-time-min="brewing.boil_time" :steps="steps" :now-ms="nowMs" />

    <div class="boil-panel__rows">
      <DataRow
        v-for="step in steps"
        :key="step.id"
        :label="step.field"
        :quantity="formatQuantity(step.quantity, step.unit)"
        :meta="rowMeta(step)"
        :state="rowState(step)"
        :checked="step.status"
        :disabled="readonly"
        @toggle="emit('toggle', step)"
      />
    </div>

    <div v-if="modalOpen" class="boil-panel__modal" @click.self="modalOpen = false">
      <div class="panel boil-panel__modal-card">
        <label class="boil-panel__modal-label" for="boil-time">{{ $t('Change Time') }} (min)</label>
        <input id="boil-time" v-model.number="newBoilTime" type="number" min="1" class="boil-panel__modal-input" />
        <div class="boil-panel__modal-actions">
          <button type="button" class="btn btn-secondary" @click="modalOpen = false">{{ $t('Cancel') }}</button>
          <button type="button" class="btn btn-primary" @click="saveBoilTime">{{ $t('Save') }}</button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.boil-panel {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.boil-panel__chrono-block {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
  text-align: center;
}

.boil-panel__chrono {
  font-size: var(--text-chrono);
  line-height: 1;
  color: var(--color-text-dim);
}

.boil-panel__chrono--signal {
  color: var(--color-signal);
}

.boil-panel__chrono--ember {
  color: var(--color-ember);
}

.boil-panel__state {
  font-family: var(--font-display);
  text-transform: uppercase;
  letter-spacing: -0.02em;
  font-size: var(--text-label);
  color: var(--color-text-dim);
}

.boil-panel__actions {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  width: 100%;
}

@media (min-width: 480px) {
  .boil-panel__actions {
    flex-direction: row;
  }
}

.boil-panel__actions .btn {
  flex: 1;
  min-width: 0;
}

.boil-panel__rows {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.boil-panel__modal {
  position: fixed;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  z-index: 50;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: rgb(14 14 20 / 70%);
  padding: 1rem;
}

.boil-panel__modal-card {
  width: 100%;
  max-width: 20rem;
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.boil-panel__modal-label {
  font-size: var(--text-label);
  color: var(--color-text-dim);
}

.boil-panel__modal-input {
  background-color: var(--color-panel-hi);
  border: 1px solid var(--color-rule);
  border-radius: var(--radius);
  padding: 0.5rem 0.75rem;
  color: var(--color-text);
  font-family: var(--font-mono);
}

.boil-panel__modal-actions {
  display: flex;
  flex-wrap: wrap;
  justify-content: flex-end;
  gap: 0.5rem;
}
</style>
