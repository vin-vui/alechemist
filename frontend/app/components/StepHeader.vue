<script setup lang="ts">
// En-tete d'un ecran de brassin, monte sur la charpente commune (PageHeader).
//
// La recette est nommee ici, en lien : c'est le seul rappel de ce qu'on brasse, et
// le seul chemin vers la fiche depuis l'ecran ou l'on brasse.
import type { Brewing } from '~/types/api'

const props = defineProps<{ brewing: Brewing; title: string }>()
const emit = defineEmits<{ 'note-saved': [note: string] }>()

const { t } = useI18n()

// « Note » ne disait pas ce que fait le bouton. Le libelle suit l'etat du brassin.
const noteLabel = computed(() => (props.brewing.note ? 'Edit the note' : 'Add a note'))
const recipeName = computed(() => props.brewing.recipe?.name ?? t('Recipe'))

const noteOpen = ref(false)
</script>

<template>
  <PageHeader :title="brewing.name">
    <template #meta>
      <!-- P5 : le nom d'etape n'est pas repete ici, la StepSpine juste en dessous
           l'indique deja en --color-signal (un seul signal par ecran). -->
      <p class="step-header__meta">
        <NuxtLink :to="`/recipes/${brewing.recipe_id}`" class="step-header__recipe">{{ recipeName }}</NuxtLink>
        <span class="step-header__sep">-</span>
        <span class="data">{{ formatDate(brewing.date_start) }}</span>
        <span class="step-header__sep">-</span>
        <span class="data">{{ formatQuantity(brewing.volume, 'l') }}</span>
      </p>
    </template>
    <template #action>
      <!-- Icone seule sur telephone : en bouton pleine largeur, la note mangeait
           une rangee entiere de l'ecran ou l'on brasse. Le libelle reapparait des
           que la place existe, et reste annonce aux lecteurs d'ecran. -->
      <button
        type="button"
        class="btn btn-secondary btn--icon"
        :aria-label="$t(noteLabel)"
        :title="$t(noteLabel)"
        @click="noteOpen = true"
      >
        <AppIcon name="note" />
        <span class="btn__label">{{ $t(noteLabel) }}</span>
      </button>
    </template>
  </PageHeader>

  <NoteDialog
    :brewing="brewing"
    :open="noteOpen"
    @close="noteOpen = false"
    @saved="emit('note-saved', $event)"
  />
</template>

<style scoped>
.step-header__meta {
  font-size: var(--text-label);
  color: var(--color-text-dim);
  display: flex;
  gap: 0.375rem;
  flex-wrap: wrap;
}

.step-header__recipe {
  color: var(--color-text-dim);
  text-decoration: underline;
  text-underline-offset: 2px;
}

.step-header__recipe:hover {
  color: var(--color-text);
}

.step-header__sep {
  color: var(--color-text-faint);
}
</style>
