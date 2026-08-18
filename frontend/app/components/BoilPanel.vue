<script setup lang="ts">
// Ecran signature : Chrono + BoilTrack + DataRow (docs/design-system.md #2, #3, #4).
// Logique de chrono, de mutation et de gating inchangee ; seul l'habillage change.
import type { Brewing, BrewingStep } from '~/types/api'
import { boilRemainingMs, canStartAromaTimer, isStepLate, stepRemainingMinutes } from '~/utils/boil'
import { runningTimerStep, timerRemainingMs, timerState } from '~/utils/stepTimer'
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

// Un aromatique entre a l'arret du feu et infuse hors flamme : il porte son propre
// chrono, comme les paliers d'empatage (utils/stepTimer). Des qu'il tourne, c'est
// lui que le grand chrono decompte -- le feu est coupe, le seul temps qui court
// encore est le sien, et l'ecran ne montre jamais deux chronos a la fois.
const runningAroma = computed(() => runningTimerStep(props.steps))
const aromaRemainingMs = computed(() => (runningAroma.value ? timerRemainingMs(runningAroma.value, nowMs.value) : null))

// Le chrono affiche toujours le temps restant une fois demarre, la duree totale
// avant : les chiffres ne s'animent pas, seule la BoilTrack progresse visuellement.
const chronoDisplayMs = computed(() => {
  if (runningAroma.value) return Math.max(0, aromaRemainingMs.value ?? 0)
  return started.value ? Math.max(0, remainingMs.value ?? 0) : props.brewing.boil_time * 60_000
})
const chronoLabel = computed(() => formatDuration(chronoDisplayMs.value))
const chronoElapsed = computed(() =>
  runningAroma.value ? (aromaRemainingMs.value ?? 0) <= 0 : started.value && elapsed.value,
)
const chronoUrgent = computed(() => {
  const ms = runningAroma.value ? aromaRemainingMs.value : started.value ? remainingMs.value : null
  return ms !== null && ms > 0 && ms < 60_000
})

const modalOpen = ref(false)
const cancelOpen = ref(false)
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

async function startAroma(step: BrewingStep) {
  try {
    await apiSend(`/api/brewing-steps/${step.id}/start`, 'POST')
    mutationError.value = false
    emit('refresh')
  } catch {
    mutationError.value = true
  }
}

// Un chrono lance par erreur n'avait aucune sortie : les deux boutons
// disparaissaient et le brassin partait sur une heure de depart fausse. La sortie
// vise ce qui tourne : l'infusion en cours s'il y en a une, sinon l'ebullition.
async function cancelRunningTimer() {
  const aroma = runningAroma.value
  try {
    if (aroma) {
      await apiSend(`/api/brewing-steps/${aroma.id}/start`, 'DELETE')
    } else {
      await apiSend(`/api/brewings/${props.brewing.id}/start-boil`, 'DELETE')
    }
    mutationError.value = false
    cancelOpen.value = false
    emit('refresh')
  } catch {
    mutationError.value = true
    cancelOpen.value = false
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

function rowState(step: BrewingStep): 'pending' | 'done' | 'late' | 'running' {
  if (step.status) return 'done'
  // Un aromatique lance suit son propre chrono ; les autres lignes restent jugees
  // sur l'echeance lue le long de la BoilTrack.
  if (step.type === 'Aroma' && step.started_at) return timerState(step, nowMs.value)
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
    // Une fois l'infusion lancee, la ligne dit ou en est son chrono plutot que de
    // repeter la consigne de depart.
    const state = timerState(step, nowMs.value)
    if (state === 'running') return t('in progress')
    if (state === 'late') return t('Time elapsed')
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
    <p v-if="mutationError" class="banner-error">{{ $t('Boil not saved') }}</p>

    <div class="boil-panel__chrono-block">
      <p
        class="boil-panel__chrono data"
        :class="{
          'boil-panel__chrono--signal': (started || runningAroma) && !chronoElapsed && !chronoUrgent,
          'boil-panel__chrono--ember': chronoUrgent || chronoElapsed,
        }"
      >
        {{ chronoLabel }}
      </p>
      <template v-if="started || runningAroma">
        <p class="boil-panel__state">
          <template v-if="runningAroma">{{ runningAroma.field }} - </template>
          {{ chronoElapsed ? $t('Time elapsed') : $t('in progress') }}
        </p>
        <!-- Sortie de secours : un chrono lance par erreur n'avait aucun recours,
             les deux boutons ayant disparu au demarrage. -->
        <button v-if="!readonly" type="button" class="boil-panel__cancel" @click="cancelOpen = true">
          {{ $t('Cancel timer') }}
        </button>
      </template>
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
      >
        <!-- L'infusion hors flamme ne se lance qu'une fois le feu coupe : avant,
             le bouton n'existe pas, et le serveur refuserait de toute facon. -->
        <template
          v-if="!readonly && canStartAromaTimer(brewing.boil_start, brewing.boil_time, step, nowMs)"
          #action
        >
          <button type="button" class="btn btn-secondary boil-panel__start" @click.stop.prevent="startAroma(step)">
            {{ $t('Start step') }}
          </button>
        </template>
      </DataRow>
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

    <ConfirmDialog
      :open="cancelOpen"
      :title="runningAroma ? $t('Cancel this step timer?') : $t('Cancel this timer?')"
      :detail="runningAroma ? $t('The step will start over from zero.') : $t('The boil will start over from zero.')"
      :confirm-label="$t('Reset')"
      @confirm="cancelRunningTimer"
      @close="cancelOpen = false"
    />
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

/* Discret : le chrono qui tourne est le signal de l'ecran, pas sa sortie de secours. */
.boil-panel__cancel {
  min-height: 44px;
  font-size: var(--text-label);
  color: var(--color-text-dim);
}

.boil-panel__cancel:hover {
  color: var(--color-ember);
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

/* Cible tactile pleine hauteur dans une ligne de 56px, sans la faire grandir. */
.boil-panel__start {
  min-height: 40px;
  padding-block: 0.25rem;
  padding-inline: 0.75rem;
  font-size: var(--text-label);
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
