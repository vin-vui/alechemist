<script setup lang="ts">
import type { Recipe } from '~/types/api'

const { data: recipes, error } = await useAsyncData('recipes', () => apiGet<Recipe[]>('/api/recipes'))
if (error.value) {
  throw createError({ statusCode: error.value.statusCode ?? 500, statusMessage: error.value.statusMessage ?? error.value.message })
}
</script>

<template>
  <div class="recipes">
    <h1 class="recipes__title">{{ $t('Recipes') }}</h1>

    <p v-if="(recipes ?? []).length === 0" class="recipes__empty">
      {{ $t('No recipes yet') }}
      <NuxtLink to="/recipes/import" class="recipes__empty-link">{{ $t('Import') }} &rarr;</NuxtLink>
    </p>

    <div v-else class="recipes__grid">
      <NuxtLink
        v-for="recipe in recipes ?? []"
        :key="recipe.id"
        :to="`/recipes/${recipe.id}`"
        class="panel recipes__card"
        :style="{ '--recipe-color': ebcToHex(recipe.color) }"
      >
        <p class="recipes__card-name">{{ recipe.name }}</p>
        <p class="recipes__card-meta">
          <span>{{ recipe.type }}</span>
          <span class="recipes__sep">-</span>
          <span class="data">{{ $t('n_brewings', recipe.brewings_count ?? 0) }}</span>
        </p>
      </NuxtLink>
    </div>

    <NuxtLink to="/recipes/import" class="btn btn-primary recipes__import" :title="$t('Import')">
      {{ $t('Import') }}
    </NuxtLink>
  </div>
</template>

<style scoped>
.recipes {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.recipes__title {
  font-family: var(--font-display);
  text-transform: uppercase;
  letter-spacing: -0.02em;
  font-weight: 800;
  font-size: 1.5rem;
}

.recipes__empty {
  color: var(--color-text-dim);
  display: flex;
  flex-wrap: wrap;
  align-items: baseline;
  gap: 0.5rem;
}

.recipes__empty-link {
  color: var(--color-signal);
}

.recipes__grid {
  display: grid;
  gap: 0.75rem;
  grid-template-columns: 1fr;
  padding-bottom: 4rem;
}

@media (min-width: 640px) {
  .recipes__grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (min-width: 1024px) {
  .recipes__grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

.recipes__card {
  display: block;
  padding: 1rem;
  border-left: 4px solid var(--recipe-color, var(--color-rule));
  transition: background-color 0.15s ease;
}

.recipes__card:hover {
  background-color: var(--color-panel-hi);
}

.recipes__card-name {
  font-family: var(--font-display);
  text-transform: uppercase;
  letter-spacing: -0.02em;
  font-weight: 700;
  color: var(--color-text);
}

.recipes__card-meta {
  margin-top: 0.375rem;
  font-size: var(--text-label);
  color: var(--color-text-dim);
}

.recipes__sep {
  margin-inline: 0.375rem;
  color: var(--color-text-faint);
}

.recipes__import {
  position: fixed;
  /* Degage la barre basse fixe mobile (AppSidebar .app-tabbar), memes valeurs que
     le padding-bottom de .app-shell__main (layouts/default.vue). */
  bottom: calc(4.5rem + env(safe-area-inset-bottom) + 1rem);
  right: 1.5rem;
  border-radius: 9999px;
  z-index: 30;
}

@media (min-width: 1024px) {
  .recipes__import {
    bottom: 1.5rem;
  }
}
</style>
