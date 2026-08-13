<script setup lang="ts">
// BoilTrack : LA signature de l'app (docs/design-system.md #2). Piste temporelle
// horizontale de T-boil_time au flame-out. Purement visuelle et decorative
// (aria-hidden) : le detail accessible (nom, quantite, echeance en texte) est
// porte par les DataRow affichees juste dessous, qui listent les memes steps.
import type { BrewingStep } from '~/types/api'
import { boilProgressPercent, boilStepPositionPercent, isStepDueSoon, isStepLate } from '~/utils/boil'

const props = defineProps<{
  boilStart: string | null
  boilTimeMin: number
  steps: BrewingStep[]
  nowMs: number
}>()

const playheadPercent = computed(() => boilProgressPercent(props.boilStart, props.boilTimeMin, props.nowMs))

type MarkerState = 'done' | 'late' | 'due' | 'upcoming'

function markerState(step: BrewingStep): MarkerState {
  if (step.status) return 'done'
  if (isStepLate(props.boilStart, props.boilTimeMin, step, props.nowMs)) return 'late'
  if (isStepDueSoon(props.boilStart, props.boilTimeMin, step, props.nowMs)) return 'due'
  return 'upcoming'
}

function markerPosition(step: BrewingStep): number {
  return boilStepPositionPercent(props.boilTimeMin, step)
}

// Les libelles sont centres sur leur repere (translateX(-50%)) : aux extremites
// (0% / 100%), la moitie du libelle deborde hors de la piste et provoque un
// scroll horizontal de page sur mobile (D2). On ancre a gauche pres de T-debut,
// a droite pres du flame-out, et on garde le centrage ailleurs.
const EDGE_THRESHOLD = 10

function markerLabelAnchor(step: BrewingStep): 'start' | 'center' | 'end' {
  const pct = markerPosition(step)
  if (pct <= EDGE_THRESHOLD) return 'start'
  if (pct >= 100 - EDGE_THRESHOLD) return 'end'
  return 'center'
}
</script>

<template>
  <div class="boil-track" aria-hidden="true">
    <!-- Reperes de temps au-dessus du rail, noms de houblon dessous (anatomie du contrat). -->
    <div class="boil-track__ends">
      <span class="data">T-{{ boilTimeMin }}</span>
      <span>{{ $t('flame-out') }}</span>
    </div>
    <div class="boil-track__rail">
      <div class="boil-track__progress" :style="{ width: `${playheadPercent}%` }" />
      <div class="boil-track__head" :style="{ left: `${playheadPercent}%` }" />
      <div
        v-for="step in steps"
        :key="step.id"
        class="boil-track__marker"
        :class="`boil-track__marker--${markerState(step)}`"
        :style="{ left: `${markerPosition(step)}%` }"
      >
        <span class="boil-track__dot" />
        <span class="boil-track__label" :class="`boil-track__label--${markerLabelAnchor(step)}`">{{ step.field }}</span>
      </div>
    </div>
  </div>
</template>

<style scoped>
/*
 * Filet de securite (D2) : composant purement decoratif (aria-hidden), l'info
 * horaire accessible vit dans les DataRow juste dessous. Le rognage horizontal
 * ne masque donc aucun contenu reel ; il protege contre un depassement residuel
 * si un repere tombe tres pres d'un bord malgre l'ancrage des libelles ci-dessous.
 */
.boil-track {
  overflow-x: hidden;
}

.boil-track__ends {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.75rem;
  font-size: var(--text-label);
  color: var(--color-text-dim);
}

.boil-track__rail {
  position: relative;
  height: 2px;
  background-color: var(--color-rule);
  margin-left: 6px;
  margin-right: 6px;
  /* Reserve la place pour les libelles de repere positionnes sous le rail. */
  margin-bottom: 2rem;
}

.boil-track__progress {
  position: absolute;
  top: 0;
  bottom: 0;
  left: 0;
  background-color: var(--color-signal);
  transition: width 1s linear;
}

.boil-track__head {
  position: absolute;
  top: 50%;
  width: 2px;
  height: 12px;
  background-color: var(--color-signal);
  transform: translate(-50%, -50%);
  transition: left 1s linear;
}

.boil-track__marker {
  position: absolute;
  top: 0;
  transform: translate(-50%, -50%);
  display: flex;
  flex-direction: column;
  align-items: center;
}

.boil-track__dot {
  width: 12px;
  height: 12px;
  border-radius: 9999px;
  background-color: var(--color-text-dim);
  display: block;
}

.boil-track__label {
  position: absolute;
  top: 100%;
  left: 50%;
  transform: translateX(-50%);
  margin-top: 0.5rem;
  font-family: var(--font-mono);
  font-size: var(--text-label);
  color: var(--color-text-dim);
  white-space: nowrap;
}

/* Ancrage aux extremites (D2) : le premier repere colle au bord gauche de la
   piste (comme "T-60" au-dessus), le dernier au bord droit (comme "FLAME-OUT"),
   au lieu de deborder de moitie hors ecran quand ils sont centres a 0%/100%. */
.boil-track__label--start {
  left: 0;
  transform: translateX(0);
}

.boil-track__label--end {
  left: auto;
  right: 0;
  transform: translateX(0);
}

.boil-track__marker--upcoming .boil-track__dot {
  background-color: var(--color-text-dim);
}

.boil-track__marker--due .boil-track__dot {
  background-color: var(--color-signal);
  animation: boil-track-pulse 1.6s ease-in-out infinite;
}

.boil-track__marker--late .boil-track__dot {
  background-color: var(--color-ember);
}

.boil-track__marker--done .boil-track__dot {
  background-color: var(--color-malt);
  opacity: 0.5;
}

.boil-track__marker--done .boil-track__label {
  opacity: 0.5;
}

@keyframes boil-track-pulse {
  0%, 100% {
    box-shadow: 0 0 0 0 color-mix(in srgb, var(--color-signal) 55%, transparent);
  }
  50% {
    box-shadow: 0 0 0 6px color-mix(in srgb, var(--color-signal) 0%, transparent);
  }
}
</style>
