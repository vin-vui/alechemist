<script setup lang="ts">
/*
 * L'action principale d'un ecran, ancree en bas, sous le pouce.
 *
 * Elle etait `sticky` : arrivee en fin de page, la barre retrouvait sa place dans
 * le flux et remontait avec le contenu, et il fallait un rideau de peinture pour
 * masquer la bande qui apparaissait alors entre elle et la barre de navigation.
 * Elle est desormais fixe, immobile au defilement.
 *
 * Une cale de meme hauteur reste dans le flux, sinon la fin du contenu se cache
 * derriere la barre. Sa hauteur est mesuree et non ecrite en dur : la barre
 * grandit d'une ligne quand le bouton est desactive et affiche sa raison.
 */
withDefaults(defineProps<{ reason?: string | null }>(), { reason: null })

const bar = ref<HTMLElement | null>(null)
const height = ref(0)
let observer: ResizeObserver | null = null

onMounted(() => {
  if (!bar.value) return
  observer = new ResizeObserver(() => {
    height.value = bar.value?.getBoundingClientRect().height ?? 0
  })
  observer.observe(bar.value)
})

onBeforeUnmount(() => {
  observer?.disconnect()
  observer = null
})
</script>

<template>
  <div class="page-action-space" :style="{ height: `${height}px` }">
    <div ref="bar" class="page-action">
      <div class="page-action__inner">
        <div class="page-action__row">
          <!-- L'annulation d'un formulaire : c'est la sortie de l'ecran, a cote de
               l'action qu'elle annule, et jamais pleine largeur, pour ne pas
               rivaliser avec elle. -->
          <div v-if="$slots.cancel" class="page-action__cancel">
            <slot name="cancel" />
          </div>
          <div class="page-action__main">
            <slot />
          </div>
        </div>
        <!-- Un bouton desactive dit toujours pourquoi (contrat design-system.md #6). -->
        <p v-if="reason" class="page-action__reason">{{ reason }}</p>
      </div>
    </div>
  </div>
</template>

<style scoped>
.page-action-space {
  flex: 0 0 auto;
}

.page-action {
  position: fixed;
  left: 0;
  right: 0;
  /* Pose exactement sur la barre de navigation, dont la hauteur est declaree une
     seule fois (--nav-height, main.css) : aucune bande a rattraper entre les deux. */
  bottom: calc(var(--nav-height) + env(safe-area-inset-bottom));
  z-index: 20;
  padding: 0.75rem 1rem;
  border-top: 1px solid var(--color-rule);
  background-color: var(--color-ink);
}

/* Le rail occupe les 12.5rem de gauche : la barre part a sa droite pour que son
   contenu se centre sur la meme colonne que celui de la page. */
@media (min-width: 1024px) {
  .page-action {
    left: 12.5rem;
    padding-inline: 1.5rem;
  }
}

.page-action__inner {
  max-width: 40rem;
  margin-inline: auto;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.page-action__row {
  display: flex;
  gap: 0.5rem;
}

.page-action__main {
  flex: 1 1 auto;
  min-width: 0;
}

.page-action__cancel {
  flex: 0 0 auto;
}

.page-action :deep(.btn) {
  width: 100%;
}

.page-action__reason {
  font-size: var(--text-label);
  color: var(--color-text-faint);
  text-align: center;
}
</style>
