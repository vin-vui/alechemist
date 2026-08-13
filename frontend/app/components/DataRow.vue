<script setup lang="ts">
// DataRow : la ligne de donnee du contrat (docs/design-system.md #4).
// Une ligne = une action ou une mesure. Case optionnelle (absente en lecture
// seule sur une recette : la quantite passe alors a gauche). Toute la ligne est
// cliquable quand une case est presente.
withDefaults(
  defineProps<{
    label: string
    quantity?: string | null
    meta?: string | null
    state?: 'pending' | 'done' | 'late'
    checkable?: boolean
    checked?: boolean
    disabled?: boolean
  }>(),
  {
    quantity: null,
    meta: null,
    state: 'pending',
    checkable: true,
    checked: false,
    disabled: false,
  },
)

const emit = defineEmits<{ toggle: [] }>()
</script>

<template>
  <label
    v-if="checkable"
    class="data-row"
    :class="[`data-row--${state}`, { 'data-row--disabled': disabled }]"
  >
    <input
      type="checkbox"
      class="data-row__checkbox"
      :checked="checked"
      :disabled="disabled"
      @change="emit('toggle')"
    />
    <span v-if="quantity" class="data-row__quantity data">{{ quantity }}</span>
    <span class="data-row__label" :title="label">{{ label }}</span>
    <span v-if="meta" class="data-row__meta data" :title="meta">{{ meta }}</span>
  </label>
  <div v-else class="data-row" :class="`data-row--${state}`">
    <span v-if="quantity" class="data-row__quantity data">{{ quantity }}</span>
    <span class="data-row__label" :title="label">{{ label }}</span>
    <span v-if="meta" class="data-row__meta data" :title="meta">{{ meta }}</span>
  </div>
</template>

<style scoped>
.data-row {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  min-height: 56px;
  background-color: var(--color-panel);
  border-radius: var(--radius);
  border-left: 3px solid transparent;
  padding-inline: 1rem;
  cursor: pointer;
}

div.data-row {
  cursor: default;
}

.data-row--disabled {
  cursor: default;
}

.data-row--done {
  border-left-color: var(--color-malt);
}

.data-row--late {
  border-left-color: var(--color-ember);
}

.data-row__checkbox {
  width: 20px;
  height: 20px;
  min-width: 20px;
  accent-color: var(--color-signal);
}

.data-row__quantity {
  min-width: 3.5rem;
  flex: 0 0 auto;
  color: var(--color-text-dim);
}

/*
 * label et meta peuvent tous les deux se comprimer (min-width: 0) et tronquent
 * plutot que de deborder les uns sur les autres si le texte est trop long pour
 * la largeur disponible (ligne entiere fixe a 56px, jamais sur deux lignes).
 */
.data-row__label {
  flex: 1 1 auto;
  min-width: 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  color: var(--color-text);
}

.data-row__meta {
  flex: 0 1 auto;
  min-width: 0;
  max-width: 60%;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  text-align: right;
  color: var(--color-text-dim);
}

.data-row--late .data-row__meta {
  color: var(--color-ember);
}
</style>
