<script setup lang="ts">
/*
 * StepSpine : la colonne vertebrale du brassin (docs/design-system.md #1).
 *
 * La version en points relies etait illisible sur telephone : reperes de 12px,
 * libelles de 11px et traits d'un pixel, le tout dans une bande de 20px de haut.
 * Elle disait « voici cinq etapes » sans repondre a la seule question du brasseur,
 * « ou j'en suis ». C'est desormais l'inverse : l'etape courante est nommee en
 * grand, et les cinq segments donnent le reste d'un coup d'oeil.
 *
 * Un segment franchi reste cliquable, en arriere seulement : on ne saute pas une
 * etape qu'on n'a pas atteinte.
 */
import type { Brewing } from '~/types/api'
import { STEP_ORDER } from '~/utils/steps'

const props = defineProps<{ brewing: Brewing; active: string }>()

const slugs = STEP_ORDER.slice(0, 5)

const currentIndex = computed(() =>
  STEP_ORDER.indexOf(props.brewing.current_step ?? 'preparation'))

function stateOf(index: number): 'done' | 'current' | 'upcoming' {
  if (index < currentIndex.value) return 'done'
  if (index === currentIndex.value) return 'current'
  return 'upcoming'
}

// Position affichee : bornee a 5, car `completed` sort de la liste des 5 etapes.
const position = computed(() => Math.min(currentIndex.value + 1, slugs.length))
const currentSlug = computed(() => slugs[Math.min(currentIndex.value, slugs.length - 1)]!)
</script>

<template>
  <nav class="step-spine" :aria-label="$t('Brewing steps')">
    <p class="step-spine__count">{{ $t('step_of', { n: position, total: slugs.length }) }}</p>
    <p class="step-spine__current">{{ $t(currentSlug) }}</p>

    <ol class="step-spine__list">
      <li v-for="(slug, index) in slugs" :key="slug" class="step-spine__item">
        <NuxtLink
          v-if="index < currentIndex"
          :to="`/recipes/${brewing.recipe_id}/brewings/${brewing.id}/${slug}`"
          class="step-spine__segment step-spine__segment--done"
        >
          <span class="step-spine__label">{{ $t(slug) }}</span>
        </NuxtLink>
        <span
          v-else
          class="step-spine__segment"
          :class="`step-spine__segment--${stateOf(index)}`"
          :aria-current="slug === active ? 'step' : undefined"
        >
          <span class="step-spine__label">{{ $t(slug) }}</span>
        </span>
      </li>
    </ol>
  </nav>
</template>

<style scoped>
.step-spine {
  display: flex;
  flex-direction: column;
  gap: 0.125rem;
}

.step-spine__count {
  font-family: var(--font-display);
  text-transform: uppercase;
  letter-spacing: -0.02em;
  font-size: var(--text-label);
  color: var(--color-text-dim);
}

/* La reponse a « ou j'en suis », en toutes lettres. */
.step-spine__current {
  font-family: var(--font-display);
  text-transform: uppercase;
  letter-spacing: -0.02em;
  font-weight: 800;
  font-size: 1.5rem;
  line-height: 1.1;
  color: var(--color-signal);
}

.step-spine__list {
  display: flex;
  gap: 0.375rem;
  margin-top: 0.5rem;
}

.step-spine__item {
  flex: 1;
  min-width: 0;
}

/*
 * La barre visible fait 6px, mais la cible tactile en fait 44 : c'est par elle
 * qu'on revient consulter une etape franchie.
 */
.step-spine__segment {
  display: flex;
  flex-direction: column;
  justify-content: center;
  min-height: 44px;
  position: relative;
}

.step-spine__segment::before {
  content: '';
  display: block;
  height: 6px;
  border-radius: 9999px;
  background-color: var(--color-rule);
}

.step-spine__segment--done::before {
  background-color: var(--color-malt);
}

.step-spine__segment--current::before {
  background-color: var(--color-signal);
}

a.step-spine__segment:hover::before {
  background-color: var(--color-text);
}

/*
 * Les libelles ne tiennent pas sous cinq segments de 67px : sur telephone, l'etape
 * courante est deja nommee en grand au-dessus, ils ne servent qu'aux lecteurs
 * d'ecran. Ils reapparaissent des que la place existe.
 */
.step-spine__label {
  position: absolute;
  width: 1px;
  height: 1px;
  margin-top: -1px;
  overflow: hidden;
  clip-path: inset(50%);
  white-space: nowrap;
}

@media (min-width: 640px) {
  .step-spine__label {
    position: static;
    width: auto;
    height: auto;
    margin-top: 0.5rem;
    overflow: visible;
    clip-path: none;
    white-space: normal;
    font-family: var(--font-display);
    text-transform: uppercase;
    letter-spacing: -0.02em;
    font-size: var(--text-label);
    color: var(--color-text-faint);
    overflow-wrap: break-word;
  }

  .step-spine__segment--done .step-spine__label {
    color: var(--color-text-dim);
  }

  .step-spine__segment--current .step-spine__label {
    color: var(--color-signal);
  }
}
</style>
