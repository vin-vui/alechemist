<script setup lang="ts">
// Bouton d'etape suivante (docs/design-system.md #6) : desactive, il affiche
// toujours la raison (`reason`) plutot que de laisser un cul-de-sac muet.
withDefaults(defineProps<{ enabled: boolean; endOfBrewing: boolean; reason?: string | null }>(), {
  reason: null,
})
const emit = defineEmits<{ next: [] }>()
</script>

<template>
  <div class="next-button">
    <button v-if="enabled" type="button" class="btn btn-primary next-button__btn" @click="emit('next')">
      {{ endOfBrewing ? $t('end of brewing') : $t('Next step') }}
    </button>
    <template v-else>
      <button type="button" class="btn next-button__btn" disabled>
        {{ endOfBrewing ? $t('end of brewing') : $t('Next step') }}
      </button>
      <p v-if="reason" class="next-button__reason">{{ reason }}</p>
    </template>
  </div>
</template>

<style scoped>
.next-button {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.next-button__btn {
  width: 100%;
}

.next-button__reason {
  font-size: var(--text-label);
  color: var(--color-text-faint);
  text-align: center;
}
</style>
