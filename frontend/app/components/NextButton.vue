<script setup lang="ts">
defineProps<{ enabled: boolean; endOfBrewing: boolean }>()
const emit = defineEmits<{ next: [] }>()
</script>

<template>
  <button
    v-if="enabled"
    type="button"
    class="btn-yellow w-full rounded py-3 font-semibold"
    @click="emit('next')"
  >
    {{ endOfBrewing ? $t('end of brewing') : $t('Next step') }}
  </button>
  <div v-else class="flex w-full items-center justify-center rounded bg-gray-100 py-3 text-gray-400">
    {{ endOfBrewing ? $t('end of brewing') : $t('Next step') }}
  </div>
</template>
