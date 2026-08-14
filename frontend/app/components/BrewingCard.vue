<script setup lang="ts">
/*
 * La carte de brassin, partagee par les endroits qui en affichent : le tableau de
 * bord (brassins en cours et archive repliee) et la liste d'une recette. Ces listes
 * avaient chacune leur propre balisage, d'ou des cartes tantot avec filet EBC tantot
 * sans, et des pastilles d'etape tantot en majuscules tantot non.
 *
 * C'est aussi le seul endroit d'ou l'on supprime un brassin, et seulement sur la
 * liste d'une recette : la suppression vivait sur l'ecran de note, collee au bouton
 * d'enregistrement.
 */
import type { Brewing, Recipe } from '~/types/api'

const props = withDefaults(
  defineProps<{
    brewing: Brewing
    /** Recette de repli quand la liste ne la charge pas sur chaque brassin. */
    recipe?: Recipe | null
    deletable?: boolean
  }>(),
  { recipe: null, deletable: true },
)

defineEmits<{ delete: [] }>()

const recipeOf = computed(() => props.brewing.recipe ?? props.recipe)
const finished = computed(() => props.brewing.current_step === 'completed')
</script>

<template>
  <div class="brewing-card" :style="{ '--brewing-color': ebcToHex(recipeOf?.color ?? null) }">
    <NuxtLink :to="brewingPath(brewing)" class="brewing-card__link">
      <p class="brewing-card__name">{{ brewing.name }}</p>
      <p v-if="recipeOf" class="brewing-card__recipe">{{ recipeOf.name }}</p>
      <p class="brewing-card__meta">
        <span class="data">{{ formatDate(brewing.date_start) }}</span>
        <span class="brewing-card__sep">-</span>
        <span class="data">{{ formatQuantity(brewing.volume, 'l') }}</span>
      </p>
      <p class="brewing-card__step">
        <span class="chip chip--step" :class="{ 'chip--done': finished }">
          {{ $t(brewing.current_step ?? 'preparation') }}
        </span>
      </p>
    </NuxtLink>

    <button
      v-if="deletable"
      type="button"
      class="brewing-card__delete"
      :aria-label="`${$t('delete this brewing')} : ${brewing.name}`"
      @click="$emit('delete')"
    >
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M4 7h16" />
        <path d="M9.5 7V5.5A1.5 1.5 0 0 1 11 4h2a1.5 1.5 0 0 1 1.5 1.5V7" />
        <path d="M6.5 7l.8 12a1.5 1.5 0 0 0 1.5 1.4h6.4a1.5 1.5 0 0 0 1.5-1.4l.8-12" />
      </svg>
    </button>
  </div>
</template>

<style scoped>
.brewing-card {
  position: relative;
  display: flex;
  align-items: flex-start;
  background-color: var(--color-panel);
  border: 1px solid var(--color-rule);
  border-left: 4px solid var(--brewing-color, var(--color-rule));
  border-radius: var(--radius);
  transition: background-color 0.15s ease;
}

.brewing-card:hover {
  background-color: var(--color-panel-hi);
}

/* Le lien porte tout le contenu : la carte entiere reste cliquable, sauf le bouton
   de suppression qui vit a cote de lui et non dedans (un bouton dans un lien n'est
   pas du HTML valide). */
.brewing-card__link {
  flex: 1 1 auto;
  min-width: 0;
  padding: 1rem;
}

.brewing-card__name {
  font-weight: 600;
  color: var(--color-text);
  overflow-wrap: break-word;
}

.brewing-card__recipe {
  font-size: var(--text-label);
  color: var(--color-text-dim);
}

.brewing-card__meta {
  margin-top: 0.25rem;
  font-size: var(--text-label);
  color: var(--color-text-dim);
}

.brewing-card__sep {
  color: var(--color-text-faint);
  margin-inline: 0.25rem;
}

.brewing-card__step {
  margin-top: 0.5rem;
}

/* Discret au repos : sur une liste, l'oeil cherche un brassin, pas une suppression. */
.brewing-card__delete {
  flex: 0 0 auto;
  width: 44px;
  height: 44px;
  margin: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: var(--radius);
  color: var(--color-text-faint);
  transition: color 0.15s ease, background-color 0.15s ease;
}

.brewing-card__delete:hover,
.brewing-card__delete:focus-visible {
  color: var(--color-ember);
  background-color: var(--color-panel-hi);
}

.brewing-card__delete svg {
  width: 20px;
  height: 20px;
}
</style>
