<script setup lang="ts">
// pending vient du parent (modalPending de useConfirmPassword) : l'appel reseau de
// confirm() se fait dans le composable, pas ici, donc l'etat de chargement est un
// prop plutot qu'un ref local.
const props = defineProps<{ visible: boolean; error: string; pending: boolean }>()
const emit = defineEmits<{ confirm: [password: string]; close: [] }>()
const password = ref('')
</script>

<template>
  <div v-if="visible" class="modal" @click.self="emit('close')">
    <form class="panel modal__form" @submit.prevent="emit('confirm', password); password = ''">
      <label class="modal__label" for="confirm-password">{{ $t('Confirm Password') }}</label>
      <input id="confirm-password" v-model="password" type="password" autocomplete="current-password" class="modal__input" />
      <p v-if="error" class="modal__error">{{ error }}</p>
      <button type="submit" class="btn btn-primary modal__submit" :disabled="pending">{{ $t('Confirm') }}</button>
    </form>
  </div>
</template>

<style scoped>
.modal {
  position: fixed;
  inset: 0;
  z-index: 50;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: rgba(0, 0, 0, 0.6);
  padding: 1rem;
}

.modal__form {
  width: 100%;
  max-width: 20rem;
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.modal__label {
  font-size: var(--text-label);
  color: var(--color-text-dim);
}

.modal__input {
  height: 48px;
  background-color: var(--color-panel);
  border: 1px solid var(--color-rule);
  border-radius: var(--radius);
  padding-inline: 0.875rem;
  color: var(--color-text);
  font-family: var(--font-sans);
}

.modal__error {
  font-size: var(--text-label);
  color: var(--color-ember);
}

.modal__submit {
  width: 100%;
}
</style>
