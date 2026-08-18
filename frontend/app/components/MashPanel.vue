<script setup lang="ts">
// Ecran d'empatage : un chrono par palier (docs/design-system.md #3, #4).
// L'ebullition porte un chrono unique sur le brassin, parce qu'elle est unique ;
// l'empatage enchaine des paliers de durees differentes, chacun avec son propre
// depart. Un seul tourne a la fois : on ne tient pas deux paliers en parallele
// dans une cuve, et deux gros chiffres a l'ecran ne diraient plus lequel suivre.
import type { Brewing, BrewingStep } from '~/types/api'
import { timerRemainingMs, timerState, runningTimerStep } from '~/utils/stepTimer'
import { formatDensity, formatDuration, formatQuantity, parseDecimalInput } from '~/utils/format'

const props = defineProps<{ brewing: Brewing; steps: BrewingStep[]; readonly: boolean }>()
const emit = defineEmits<{ refresh: []; toggle: [step: BrewingStep] }>()

const { t } = useI18n()

const nowMs = ref(Date.now())
let timer: ReturnType<typeof setInterval> | undefined
onMounted(() => { timer = setInterval(() => { nowMs.value = Date.now() }, 1000) })
onUnmounted(() => clearInterval(timer))

const running = computed(() => runningTimerStep(props.steps))
const remainingMs = computed(() => (running.value ? timerRemainingMs(running.value, nowMs.value) : null))
const elapsed = computed(() => remainingMs.value !== null && remainingMs.value <= 0)
const chronoLabel = computed(() => formatDuration(Math.max(0, remainingMs.value ?? 0)))
const chronoUrgent = computed(() => !elapsed.value && (remainingMs.value ?? 0) < 60_000)

const mutationError = ref(false)
const cancelOpen = ref(false)

async function start(step: BrewingStep) {
  try {
    await apiSend(`/api/brewing-steps/${step.id}/start`, 'POST')
    mutationError.value = false
    emit('refresh')
  } catch {
    mutationError.value = true
  }
}

// Sortie de secours, comme sur l'ebullition : un chrono lance sur le mauvais
// palier laisserait sinon le brassin sur une heure de depart fausse.
async function cancelTimer() {
  if (!running.value) return
  try {
    await apiSend(`/api/brewing-steps/${running.value.id}/start`, 'DELETE')
    mutationError.value = false
    cancelOpen.value = false
    emit('refresh')
  } catch {
    mutationError.value = true
    cancelOpen.value = false
  }
}

function rowState(step: BrewingStep) {
  return timerState(step, nowMs.value)
}

// Une ligne cochee est faite : plus d'echeance a afficher, comme sur l'ebullition.
function rowMeta(step: BrewingStep): string | null {
  const state = rowState(step)
  if (state === 'done') return null
  if (state === 'running') return t('in progress')
  if (state === 'late') return t('Time elapsed')
  return step.time ? `${t('for')} ${t('n_minutes', step.time)}` : null
}

// Un palier sans duree n'a rien a decompter : le bouton ne sert a rien, et le
// chrono afficherait 00:00 des le depart.
function canStart(step: BrewingStep): boolean {
  return !props.readonly && !step.status && step.time !== null && step.started_at === null
}

// --- Densite avant ebullition, mesuree dans la cuve a la fin de l'empatage. ---
// La recette porte la densite visee ; celle-ci appartient au brassin du jour.
const editingDensity = ref(false)
const densityInput = ref<string>('')
const densityError = ref(false)

function startEditDensity() {
  densityInput.value = props.brewing.before_boil_density?.toString() ?? ''
  editingDensity.value = true
}

async function saveDensity() {
  // parseDecimalInput distingue le champ vide (null, on efface) d'une saisie qui
  // n'est pas un nombre (undefined, on refuse), et accepte la virgule francaise.
  const parsed = parseDecimalInput(densityInput.value)
  if (parsed === undefined) {
    densityError.value = true
    return
  }
  try {
    await apiSend(`/api/brewings/${props.brewing.id}`, 'PATCH', { before_boil_density: parsed })
    editingDensity.value = false
    densityError.value = false
    emit('refresh')
  } catch {
    densityError.value = true
  }
}
</script>

<template>
  <div class="mash-panel">
    <p v-if="mutationError" class="banner-error">{{ $t('Step not saved') }}</p>

    <!-- Le bloc chrono n'apparait que lorsqu'un palier tourne : sans palier en
         cours, l'ecran est la liste des paliers et de leurs durees. -->
    <div v-if="running" class="mash-panel__chrono-block">
      <p
        class="mash-panel__chrono data"
        :class="{
          'mash-panel__chrono--signal': !elapsed && !chronoUrgent,
          'mash-panel__chrono--ember': chronoUrgent || elapsed,
        }"
      >
        {{ chronoLabel }}
      </p>
      <p class="mash-panel__state">
        {{ running.field }} - {{ elapsed ? $t('Time elapsed') : $t('in progress') }}
      </p>
      <button v-if="!readonly" type="button" class="mash-panel__cancel" @click="cancelOpen = true">
        {{ $t('Cancel timer') }}
      </button>
    </div>

    <div class="mash-panel__rows">
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
        <template v-if="canStart(step)" #action>
          <!-- Le bouton vit dans le label de la ligne : sans .stop.prevent, le
               demarrage cocherait la case au passage. -->
          <button type="button" class="btn btn-secondary mash-panel__start" @click.stop.prevent="start(step)">
            {{ $t('Start step') }}
          </button>
        </template>
      </DataRow>
    </div>

    <div class="panel mash-panel__density">
      <span class="mash-panel__density-label">{{ $t('Before Boil Density') }} :</span>
      <template v-if="!editingDensity">
        <span v-if="brewing.before_boil_density != null" class="data chip">
          {{ formatDensity(brewing.before_boil_density) }}
        </span>
        <span v-else class="mash-panel__density-missing">{{ $t('Not set') }}</span>
        <button v-if="!readonly" type="button" class="mash-panel__link" @click="startEditDensity">
          {{ $t('Edit') }}
        </button>
      </template>
      <template v-else>
        <input
          v-model="densityInput"
          type="text"
          inputmode="decimal"
          class="mash-panel__density-input data"
          :aria-label="$t('Before Boil Density')"
          @keyup.enter="saveDensity"
        />
        <button type="button" class="mash-panel__link" @click="saveDensity">{{ $t('Save') }}</button>
        <button type="button" class="mash-panel__link" @click="editingDensity = false">{{ $t('Cancel') }}</button>
      </template>
      <p v-if="densityError" class="banner-error mash-panel__density-error">{{ $t('Density not saved') }}</p>
    </div>

    <ConfirmDialog
      :open="cancelOpen"
      :title="$t('Cancel this step timer?')"
      :detail="$t('The step will start over from zero.')"
      :confirm-label="$t('Reset')"
      @confirm="cancelTimer"
      @close="cancelOpen = false"
    />
  </div>
</template>

<style scoped>
.mash-panel {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.mash-panel__chrono-block {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
  text-align: center;
}

.mash-panel__chrono {
  font-size: var(--text-chrono);
  line-height: 1;
  color: var(--color-text-dim);
}

.mash-panel__chrono--signal {
  color: var(--color-signal);
}

.mash-panel__chrono--ember {
  color: var(--color-ember);
}

.mash-panel__state {
  font-family: var(--font-display);
  text-transform: uppercase;
  letter-spacing: -0.02em;
  font-size: var(--text-label);
  color: var(--color-text-dim);
}

/* Discret : le chrono qui tourne est le signal de l'ecran, pas sa sortie de secours. */
.mash-panel__cancel {
  min-height: 44px;
  font-size: var(--text-label);
  color: var(--color-text-dim);
}

.mash-panel__cancel:hover {
  color: var(--color-ember);
}

.mash-panel__rows {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

/* Cible tactile pleine hauteur dans une ligne de 56px, sans la faire grandir. */
.mash-panel__start {
  min-height: 40px;
  padding-block: 0.25rem;
  padding-inline: 0.75rem;
  font-size: var(--text-label);
}

.mash-panel__density {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.5rem 0.75rem;
  padding: 1rem;
}

.mash-panel__density-label {
  font-size: var(--text-label);
  color: var(--color-text-dim);
}

.mash-panel__density-missing {
  color: var(--color-text-dim);
}

.mash-panel__density-input {
  width: 6rem;
  background-color: var(--color-panel-hi);
  border: 1px solid var(--color-rule);
  border-radius: var(--radius);
  padding: 0.5rem 0.75rem;
  color: var(--color-text);
}

.mash-panel__link {
  min-height: 44px;
  font-size: var(--text-label);
  color: var(--color-signal);
}

.mash-panel__density-error {
  flex: 1 0 100%;
}
</style>
