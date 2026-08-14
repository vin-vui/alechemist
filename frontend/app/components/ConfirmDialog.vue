<script setup lang="ts">
// Remplace les `confirm()` natifs des trois suppressions (recette, brassin depuis
// la note, brassin depuis le recap). Une boite de dialogue du navigateur au milieu
// d'une PWA, avec « Confirmer la suppression ? » pour tout message, ne disait ni
// quoi est supprime ni que c'est definitif.
const props = withDefaults(
  defineProps<{
    open: boolean
    title: string
    detail?: string | null
    confirmLabel: string
    pending?: boolean
  }>(),
  { detail: null, pending: false },
)

const emit = defineEmits<{ confirm: []; close: [] }>()

const cancelButton = ref<HTMLButtonElement | null>(null)

watch(
  () => props.open,
  async (open) => {
    if (!open) return
    await nextTick()
    // Le focus part sur « Annuler » : l'action sure est celle qu'on atteint en premier.
    cancelButton.value?.focus()
  },
)
</script>

<template>
  <div
    v-if="open"
    class="confirm-dialog"
    role="dialog"
    aria-modal="true"
    :aria-label="title"
    @click.self="emit('close')"
    @keydown.esc="emit('close')"
  >
    <div class="panel confirm-dialog__card">
      <h2 class="confirm-dialog__title">{{ title }}</h2>
      <p v-if="detail" class="confirm-dialog__detail">{{ detail }}</p>
      <div class="confirm-dialog__actions">
        <button ref="cancelButton" type="button" class="btn btn-secondary" @click="emit('close')">
          {{ $t('Cancel') }}
        </button>
        <button type="button" class="btn btn-danger confirm-dialog__confirm" :disabled="pending" @click="emit('confirm')">
          {{ confirmLabel }}
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.confirm-dialog {
  position: fixed;
  inset: 0;
  z-index: 50;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  background-color: rgb(14 14 20 / 0.8);
}

.confirm-dialog__card {
  width: 100%;
  max-width: 24rem;
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.confirm-dialog__title {
  font-family: var(--font-display);
  text-transform: uppercase;
  letter-spacing: -0.02em;
  font-weight: 700;
  font-size: 1.125rem;
}

.confirm-dialog__detail {
  color: var(--color-text-dim);
  font-size: var(--text-body);
}

.confirm-dialog__actions {
  margin-top: 0.5rem;
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
}

.confirm-dialog__actions > * {
  flex: 1 1 8rem;
}

/* Le bouton destructif porte son contour en permanence dans un dialogue : il n'y a
   pas de survol au doigt pour le reveler. */
.confirm-dialog__confirm {
  border-color: var(--color-ember);
}
</style>
