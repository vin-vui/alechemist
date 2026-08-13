<script setup lang="ts">
import type { Brewing } from '~/types/api'

defineProps<{ brewing: Brewing; title: string }>()
</script>

<template>
  <div class="panel step-header">
    <NuxtLink :to="`/recipes/${brewing.recipe_id}`" class="step-header__back">
      &larr; {{ $t('Recipes') }}
    </NuxtLink>
    <div class="step-header__row">
      <div>
        <h1 class="step-header__name">{{ brewing.name }}</h1>
        <p class="step-header__meta">
          <span class="data">{{ formatDate(brewing.date_start) }}</span>
          <span class="step-header__sep">-</span>
          <span class="data">{{ brewing.volume }} l</span>
          <span class="step-header__sep">-</span>
          <span>{{ $t(title) }}</span>
        </p>
      </div>
      <NuxtLink
        :to="`/recipes/${brewing.recipe_id}/brewings/${brewing.id}/note`"
        class="btn btn-secondary"
        :title="$t('Note')"
      >
        {{ $t('Note') }}
      </NuxtLink>
    </div>
  </div>
</template>

<style scoped>
.step-header {
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.step-header__back {
  align-self: flex-start;
  font-size: var(--text-label);
  color: var(--color-text-dim);
}

.step-header__back:hover {
  color: var(--color-text);
}

.step-header__row {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem;
}

.step-header__name {
  font-family: var(--font-display);
  text-transform: uppercase;
  letter-spacing: -0.02em;
  font-weight: 800;
  font-size: 1.5rem;
}

.step-header__meta {
  margin-top: 0.25rem;
  font-size: var(--text-label);
  color: var(--color-text-dim);
  display: flex;
  gap: 0.375rem;
  flex-wrap: wrap;
}

.step-header__sep {
  color: var(--color-text-faint);
}
</style>
