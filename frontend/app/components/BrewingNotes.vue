<script setup lang="ts">
/*
 * Les notes de tous les brassins d'une recette, sur la fiche recette.
 *
 * Une note ne se lisait que depuis le brassin qui la porte : retrouver ce qu'on
 * avait note au brassin d'avant demandait de sortir de la recette, d'ouvrir la
 * liste des brassins, puis d'en ouvrir un. Elles sont ici rassemblees en journal,
 * du dernier brassin au premier, et modifiables sur place par le meme dialogue
 * que les ecrans d'etape et le recapitulatif.
 *
 * Le composant ne charge rien : la page tient la liste des brassins et se met a
 * jour a l'enregistrement, pour que la note remonte aussi a la liste des brassins
 * qui partage la meme cle useAsyncData.
 */
import type { Brewing } from '~/types/api'

const props = defineProps<{ brewings?: Brewing[] | null }>()
const emit = defineEmits<{ saved: [payload: { id: number; note: string }] }>()

const notes = computed(() => brewingsWithNotes(props.brewings))

const editing = ref<Brewing | null>(null)

function onSaved(note: string) {
  if (editing.value) emit('saved', { id: editing.value.id, note })
}
</script>

<template>
  <!-- Pas de section vide sur une recette jamais brassee, ni sur une recette dont
       aucun brassin n'a ete annote : c'est un journal, il n'apparait qu'ecrit. -->
  <section v-if="notes.length > 0" class="recipe-notes">
    <h2 class="section-title">{{ $t('Brewing notes') }}</h2>

    <article v-for="brewing in notes" :key="brewing.id" class="panel recipe-notes__item">
      <div class="recipe-notes__head">
        <!-- Le nom mene au brassin : la note dit souvent « comme la fois d'avant »,
             et on veut alors voir le reste du brassin. -->
        <NuxtLink :to="brewingPath(brewing)" class="recipe-notes__brewing">{{ brewing.name }}</NuxtLink>
        <span class="data recipe-notes__date">{{ formatDate(brewing.date_start) }}</span>
      </div>
      <p class="recipe-notes__text">{{ brewing.note }}</p>
      <button type="button" class="recipe-notes__link" @click="editing = brewing">
        {{ $t('Edit the note') }} &rarr;
      </button>
    </article>

    <!-- Monte a l'ouverture seulement, une instance par brassin : le dialogue lit la
         note de son brassin a la construction. -->
    <NoteDialog
      v-if="editing"
      :key="editing.id"
      :brewing="editing"
      :open="true"
      @close="editing = null"
      @saved="onSaved"
    />
  </section>
</template>

<style scoped>
.recipe-notes {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.recipe-notes__item {
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.recipe-notes__head {
  display: flex;
  flex-wrap: wrap;
  align-items: baseline;
  justify-content: space-between;
  gap: 0.5rem;
}

.recipe-notes__brewing {
  color: var(--color-text);
}

.recipe-notes__brewing:hover {
  text-decoration: underline;
}

.recipe-notes__date {
  font-size: var(--text-label);
  color: var(--color-text-dim);
}

/* pre-wrap : une note est saisie dans un textarea, ses retours a la ligne sont ce
   que le brasseur a ecrit. */
.recipe-notes__text {
  white-space: pre-wrap;
  color: var(--color-text-dim);
  font-size: var(--text-body);
}

.recipe-notes__link {
  align-self: flex-start;
  min-height: 44px;
  display: inline-flex;
  align-items: center;
  font-size: var(--text-label);
  color: var(--color-signal);
}
</style>
