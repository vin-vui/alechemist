<script setup lang="ts">
/*
 * La charpente commune a tous les ecrans : un titre, ses informations, et au plus
 * une action secondaire dans le coin haut droit.
 *
 * Il n'y a plus de lien de retour. Calcule sur le trajet parcouru, il changeait de
 * libelle d'un passage a l'autre ; calque sur la hierarchie des URL, il refaisait
 * le travail de la barre de navigation. Dans les deux cas c'etait un repere qui ne
 * tient pas en place. On quitte un ecran par la barre de navigation, et un
 * formulaire par son bouton d'annulation.
 */
defineProps<{ title: string }>()
</script>

<template>
  <header class="page-header">
    <div class="page-header__titles">
      <h1 class="page-title">{{ title }}</h1>
      <slot name="meta" />
    </div>
    <!-- L'action partage la ligne du titre : sur la sienne, elle coutait 48px de
         hauteur a chaque ecran, en haut d'un telephone ou la place manque. -->
    <div v-if="$slots.action" class="page-header__action">
      <slot name="action" />
    </div>
  </header>
</template>

<style scoped>
/*
 * `flex-wrap` : un titre long (« QA Instrument IPA ») et une action longue
 * (« Liste des brassins ») ne tiennent pas ensemble sur 358px. L'action passe
 * alors sous le titre, toujours a droite, plutot que de rogner l'un des deux.
 */
.page-header {
  display: flex;
  align-items: flex-start;
  flex-wrap: wrap;
  gap: 0.5rem 1rem;
}

.page-header__titles {
  flex: 1 1 14rem;
  min-width: 0;
}

/* `margin-left: auto` et non `space-between` : quand l'action passe a la ligne,
   elle reste dans le meme coin. */
.page-header__action {
  flex: 0 0 auto;
  margin-left: auto;
}
</style>
