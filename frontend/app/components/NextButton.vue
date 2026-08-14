<script setup lang="ts">
// Bouton d'etape suivante : l'action principale de l'ecran de brassage, donc
// ancree en bas comme celle de tous les autres ecrans (PageAction). Desactive, il
// affiche toujours sa raison plutot que de laisser un cul-de-sac muet.
withDefaults(defineProps<{ enabled: boolean; endOfBrewing: boolean; reason?: string | null }>(), {
  reason: null,
})
const emit = defineEmits<{ next: [] }>()
</script>

<template>
  <PageAction :reason="enabled ? null : reason">
    <button
      type="button"
      class="btn"
      :class="{ 'btn-primary': enabled }"
      :disabled="!enabled"
      @click="enabled && emit('next')"
    >
      {{ endOfBrewing ? $t('end of brewing') : $t('Next step') }}
    </button>
  </PageAction>
</template>
