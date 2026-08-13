<script setup lang="ts">
import type { Brewing } from '~/types/api'

const route = useRoute()
const recipeId = Number(route.params.recipe)
const { data: brewings, error } = await useAsyncData(`brewings-${recipeId}`, () =>
  apiGet<Brewing[]>(`/api/recipes/${recipeId}/brewings`))
if (error.value) {
  throw createError({ statusCode: error.value.statusCode ?? 500, statusMessage: error.value.statusMessage ?? error.value.message })
}
</script>

<template>
  <div class="brewings">
    <div class="brewings__topbar">
      <h1 class="brewings__title">{{ $t('Brewing list') }}</h1>
      <NuxtLink :to="`/recipes/${recipeId}/brewings/new`" class="btn btn-primary">{{ $t('New Brewing') }}</NuxtLink>
    </div>
    <NuxtLink :to="`/recipes/${recipeId}`" class="brewings__back">&larr; {{ $t('Recipes') }}</NuxtLink>

    <p v-if="(brewings ?? []).length === 0" class="brewings__empty">
      {{ $t('No brewings for this recipe') }}
      <NuxtLink :to="`/recipes/${recipeId}/brewings/new`" class="brewings__empty-link">{{ $t('New Brewing') }} &rarr;</NuxtLink>
    </p>

    <div v-else class="brewings__grid">
      <NuxtLink
        v-for="brewing in brewings ?? []"
        :key="brewing.id"
        :to="brewingPath(brewing)"
        class="panel brewings__card"
      >
        <p class="brewings__card-name">{{ brewing.name }}</p>
        <p class="brewings__card-meta">
          <span class="data">{{ formatDate(brewing.date_start) }}</span>
          <span class="brewings__sep">-</span>
          <span class="data">{{ brewing.volume }}</span><span class="brewings__unit">l</span>
        </p>
        <p class="brewings__card-step">
          <span class="chip" :class="{ 'chip--done': brewing.current_step === 'completed' }">
            {{ $t(brewing.current_step ?? 'preparation') }}
          </span>
        </p>
      </NuxtLink>
    </div>
  </div>
</template>

<style scoped>
.brewings {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.brewings__topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
}

.brewings__title {
  font-family: var(--font-display);
  text-transform: uppercase;
  letter-spacing: -0.02em;
  font-weight: 800;
  font-size: 1.5rem;
}

.brewings__back {
  font-size: var(--text-label);
  color: var(--color-text-dim);
}

.brewings__back:hover {
  color: var(--color-text);
}

.brewings__empty {
  color: var(--color-text-dim);
  display: flex;
  flex-wrap: wrap;
  align-items: baseline;
  gap: 0.5rem;
}

.brewings__empty-link {
  color: var(--color-signal);
}

.brewings__grid {
  display: grid;
  gap: 0.75rem;
  grid-template-columns: 1fr;
}

@media (min-width: 640px) {
  .brewings__grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (min-width: 1024px) {
  .brewings__grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

.brewings__card {
  display: block;
  padding: 1rem;
  transition: background-color 0.15s ease;
}

.brewings__card:hover {
  background-color: var(--color-panel-hi);
}

.brewings__card-name {
  font-weight: 600;
  color: var(--color-text);
}

.brewings__card-meta {
  margin-top: 0.25rem;
  font-size: var(--text-label);
  color: var(--color-text-dim);
}

.brewings__sep {
  color: var(--color-text-faint);
  margin-inline: 0.25rem;
}

.brewings__unit {
  margin-left: 0.25rem;
}

.brewings__card-step {
  margin-top: 0.5rem;
}

.chip--done {
  color: var(--color-ink);
  background-color: var(--color-malt);
}
</style>
