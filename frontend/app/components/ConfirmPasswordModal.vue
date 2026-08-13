<script setup lang="ts">
// pending vient du parent (modalPending de useConfirmPassword) : l'appel reseau de
// confirm() se fait dans le composable, pas ici, donc l'etat de chargement est un
// prop plutot qu'un ref local.
const props = defineProps<{ visible: boolean; error: string; pending: boolean }>()
const emit = defineEmits<{ confirm: [password: string]; close: [] }>()
const password = ref('')
</script>

<template>
  <div v-if="visible" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="emit('close')">
    <form class="w-full max-w-xs rounded bg-white p-4 shadow" @submit.prevent="emit('confirm', password); password = ''">
      <label class="block text-sm" for="confirm-password">{{ $t('Confirm Password') }}</label>
      <input id="confirm-password" v-model="password" type="password" autocomplete="current-password" class="mt-1 w-full rounded border-gray-300" />
      <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
      <button type="submit" class="btn-yellow mt-3 w-full rounded" :disabled="pending">{{ $t('Confirm') }}</button>
    </form>
  </div>
</template>
