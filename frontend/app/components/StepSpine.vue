<script setup lang="ts">
// StepSpine : la colonne vertebrale du brassin (docs/design-system.md #1).
// Remplace StepIndicator.vue. Cliquable en arriere (consultation) uniquement,
// jamais en avant : on ne saute pas une etape qui n'a pas encore ete atteinte.
import type { Brewing } from '~/types/api'
import { STEP_ORDER } from '~/utils/steps'

const props = defineProps<{ brewing: Brewing; active: string }>()

const slugs = STEP_ORDER.slice(0, 5)

// Libelles abbreges (cf. anatomie du contrat : "prep emp EBU lev ferm") : 5 mots
// complets ("preparation".."fermentation") ne rentrent pas sous 5 points sur un
// ecran de 360px. Cles i18n dediees, distinctes de $t(slug) (deja utilise ailleurs
// pour le libelle complet, ex. le bandeau "Etape en cours").
const ABBR_KEYS: Record<string, string> = {
  preparation: 'step_abbr_preparation',
  mash: 'step_abbr_mash',
  boil: 'step_abbr_boil',
  yeast: 'step_abbr_yeast',
  ferment: 'step_abbr_ferment',
}

const currentIndex = computed(() =>
  STEP_ORDER.indexOf(props.brewing.current_step ?? 'preparation'))

function stateOf(index: number): 'done' | 'current' | 'upcoming' {
  if (index < currentIndex.value) return 'done'
  if (index === currentIndex.value) return 'current'
  return 'upcoming'
}
</script>

<template>
  <nav class="step-spine" :aria-label="$t('Brewing steps')">
    <ol class="step-spine__list">
      <li v-for="(slug, index) in slugs" :key="slug" class="step-spine__item">
        <span
          v-if="index > 0"
          class="step-spine__connector"
          :class="{ 'step-spine__connector--done': index <= currentIndex }"
        />
        <NuxtLink
          v-if="index < currentIndex"
          :to="`/recipes/${brewing.recipe_id}/brewings/${brewing.id}/${slug}`"
          class="step-spine__point step-spine__point--done"
        >
          <span class="step-spine__dot" />
        </NuxtLink>
        <span
          v-else
          class="step-spine__point"
          :class="`step-spine__point--${stateOf(index)}`"
          :aria-current="slug === active ? 'step' : undefined"
        >
          <span class="step-spine__dot" />
        </span>
        <span class="step-spine__label" :class="`step-spine__label--${stateOf(index)}`">{{ $t(ABBR_KEYS[slug] ?? slug) }}</span>
      </li>
    </ol>
    <span class="step-spine__count data">{{ Math.min(currentIndex + 1, slugs.length) }}/{{ slugs.length }}</span>
  </nav>
</template>

<style scoped>
.step-spine {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  /* Espace reserve pour les libellés positionnés sous les points. */
  margin-bottom: 1.5rem;
}

.step-spine__list {
  display: flex;
  align-items: flex-start;
  flex: 1;
  min-width: 0;
}

.step-spine__item {
  display: flex;
  align-items: center;
  flex: 1;
  min-width: 0;
  position: relative;
}

.step-spine__item:last-child {
  flex: 0 0 auto;
}

.step-spine__connector {
  flex: 1;
  height: 2px;
  min-width: 0.75rem;
  background-color: var(--color-rule);
  margin-inline: 2px;
}

.step-spine__connector--done {
  background-color: var(--color-malt);
}

.step-spine__point {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 44px;
  height: 44px;
  flex: 0 0 auto;
  border-radius: 9999px;
}

.step-spine__dot {
  width: 12px;
  height: 12px;
  border-radius: 9999px;
  border: 2px solid var(--color-rule);
  background-color: transparent;
  display: block;
}

.step-spine__point--done .step-spine__dot {
  background-color: var(--color-malt);
  border-color: var(--color-malt);
}

.step-spine__point--current .step-spine__dot {
  background-color: transparent;
  border-color: var(--color-signal);
  box-shadow: 0 0 0 2px var(--color-signal);
}

.step-spine__point--upcoming .step-spine__dot {
  background-color: transparent;
  border-color: var(--color-rule);
}

.step-spine__label {
  position: absolute;
  top: 100%;
  left: 50%;
  transform: translateX(-50%);
  margin-top: 0.25rem;
  font-family: var(--font-display);
  letter-spacing: -0.02em;
  /* Plus petit que --text-label : 5 libelles doivent tenir sous 5 points sur 360px. */
  font-size: 0.6875rem;
  white-space: nowrap;
}

.step-spine__label--done {
  color: var(--color-text-dim);
}

.step-spine__label--current {
  color: var(--color-signal);
  text-transform: uppercase;
}

.step-spine__label--upcoming {
  color: var(--color-text-faint);
}

.step-spine__count {
  color: var(--color-text-dim);
  font-size: var(--text-label);
  flex: 0 0 auto;
}
</style>
