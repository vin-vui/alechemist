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
        <span class="boil-track__label">{{ step.field }}</span>
      </div>
    </div>
  </div>
</template>

<style scoped>
/*
 * Filet de securite (D2) : composant purement decoratif (aria-hidden), l'info
 * horaire accessible vit dans les DataRow juste dessous. Le rognage horizontal
 * ne masque donc aucun contenu reel ; il protege contre le cas extreme d'un nom
 * de houblon plus large que le retrait reserve aux extremites du rail.
 */
/* `clip` et non `hidden` : `overflow-x: hidden` fait basculer l'autre axe en
   `overflow-y: auto`, et Chrome en mode mobile reserve alors une barre de
   defilement permanente de 15px a droite du bloc. Elle ne defilait rien et
   rognait la piste d'autant, decalant le rail vers la gauche. */
.boil-track {
  overflow-x: clip;
}

/* Meme retrait que le rail : « T-60 » et « arret du feu » marquent ses deux bouts,
   ils s'alignent donc sur eux et non sur ceux du bloc. */
.boil-track__ends {
  display: flex;
  justify-content: space-between;
  margin-inline: 2.5rem;
  margin-bottom: 0.75rem;
  font-size: var(--text-label);
  color: var(--color-text-dim);
}

.boil-track__rail {
  position: relative;
  height: 2px;
  background-color: var(--color-rule);
  /*
   * 2.5rem de chaque cote : tout libelle reste centre sous son rond, y compris
   * aux extremites. Les reperes de bout etaient auparavant ancres a gauche et a
   * droite pour ne pas deborder, si bien que « Cascade » et « Simcoe » ne
   * tombaient pas sous leur rond. C'est le rail qui recule, pas le libelle qui
   * se decale : la position d'un rond dit une heure, elle ne se negocie pas.
   */
  margin-inline: 2.5rem;
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

/* `top: 50%` comme la tete de lecture : cale sur l'axe du rail, et non sur son
   bord superieur, qui posait les ronds un pixel trop haut. */
.boil-track__marker {
  position: absolute;
  top: 50%;
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
