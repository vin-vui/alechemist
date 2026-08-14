<script setup lang="ts">
/*
 * Les icones de l'app, en un seul endroit. Elles partagent le meme trace : trait de
 * 1.5, extremites et jointures arrondies, grille de 24. Une icone dessinee autrement
 * se voit immediatement a cote des autres.
 */
const ICONS = {
  // Creer : commencer un brassin, ici ou depuis une recette.
  plus: '<path d="M12 5v14" /><path d="M5 12h14" />',
  // Importer un fichier : une fleche qui entre dans un bac.
  import: '<path d="M12 4v10" /><path d="m8 10 4 4 4-4" /><path d="M5 17.5v1A1.5 1.5 0 0 0 6.5 20h11a1.5 1.5 0 0 0 1.5-1.5v-1" />',
  // Recommencer : refaire la meme biere.
  repeat: '<path d="M4 12a8 8 0 0 1 13.7-5.6L20 8" /><path d="M20 4v4h-4" /><path d="M20 12a8 8 0 0 1-13.7 5.6L4 16" /><path d="M4 20v-4h4" />',
  // Note : des lignes et un crayon.
  note: '<path d="M4 6.5h11" /><path d="M4 12h7" /><path d="M4 17.5h5" /><path d="M14.5 19.5 20 14l-2-2-5.5 5.5-.5 2.5 2.5-.5Z" />',
  // Sortir.
  logout: '<path d="M14 4h3.5A1.5 1.5 0 0 1 19 5.5v13a1.5 1.5 0 0 1-1.5 1.5H14" /><path d="M10 16l-4-4 4-4" /><path d="M6 12h9" />',
  // Deplier : un chevron qui pivote d'un quart de tour a l'ouverture.
  chevron: '<path d="m9 5 7 7-7 7" />',
} as const

defineProps<{ name: keyof typeof ICONS }>()
</script>

<template>
  <svg
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    stroke-width="1.5"
    stroke-linecap="round"
    stroke-linejoin="round"
    aria-hidden="true"
    v-html="ICONS[name]"
  />
</template>
