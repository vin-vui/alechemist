<script setup lang="ts">
import type { Dashboard } from '~/types/api'

const { data: dashboard, error } = await useAsyncData('dashboard', () => apiGet<Dashboard>('/api/dashboard'))
if (error.value) {
  throw createError({ statusCode: error.value.statusCode ?? 500, statusMessage: error.value.statusMessage ?? error.value.message })
}
const now = new Date()
</script>

<template>
  <div v-if="dashboard" class="dashboard">
    <NuxtLink to="/recipes" class="panel dashboard__metric">
      <p class="dashboard__metric-value data">{{ dashboard.recipe_count }}</p>
      <p class="dashboard__metric-label">{{ $t('n_recipes', dashboard.recipe_count) }}</p>
    </NuxtLink>

    <section class="dashboard__section">
      <h2 class="dashboard__heading">{{ $t('n_fermentations', dashboard.ferment_brewings.length) }}</h2>
      <p v-if="dashboard.ferment_brewings.length === 0" class="dashboard__empty">
        {{ $t('No fermentations in progress') }}
      </p>
      <div
        v-for="brewing in dashboard.ferment_brewings"
        :key="brewing.id"
        class="panel dashboard__ferment"
        :style="{ '--brewing-color': ebcToHex(brewing.recipe?.color ?? null) }"
      >
        <NuxtLink :to="brewingPath(brewing)" class="dashboard__ferment-name">
          {{ brewing.name }} <span class="dashboard__ferment-recipe">({{ brewing.recipe?.name }})</span>
        </NuxtLink>
        <div
          v-for="view in fermentSchedule(brewing.ferment_start!, brewing.steps ?? [], now)"
          :key="view.step.id"
          class="dashboard__ferment-row"
        >
          <div class="dashboard__ferment-row-head">
            <span class="dashboard__ferment-row-label">{{ view.step.field }}</span>
            <span class="data dashboard__ferment-row-meta" :class="{ 'dashboard__ferment-row-meta--late': view.late }">
              {{ view.late ? $t('late') : $t('n_days', view.daysLeft) }} - {{ formatDate(view.endsAt.toISOString()) }}
            </span>
          </div>
          <div class="dashboard__progress-track">
            <div class="dashboard__progress-bar" :style="{ width: `${view.progress}%` }" />
          </div>
        </div>
      </div>
    </section>

    <section class="dashboard__section">
      <h2 class="dashboard__heading">{{ $t('Current step') }}</h2>
      <div v-if="dashboard.in_progress_brewings.length === 0" class="dashboard__empty">
        <p>{{ $t('No brewings in progress') }}</p>
        <NuxtLink to="/recipes" class="dashboard__empty-link">{{ $t('Recipes') }} &rarr;</NuxtLink>
      </div>
      <div v-else class="dashboard__grid">
        <NuxtLink
          v-for="brewing in dashboard.in_progress_brewings"
          :key="brewing.id"
          :to="brewingPath(brewing)"
          class="panel dashboard__brewing"
          :style="{ '--brewing-color': ebcToHex(brewing.recipe?.color ?? null) }"
        >
          <p class="dashboard__brewing-name">{{ brewing.name }}</p>
          <p class="dashboard__brewing-recipe">{{ brewing.recipe?.name }}</p>
          <p class="dashboard__brewing-meta">
            <span class="data">{{ formatDate(brewing.date_start) }}</span>
            <span class="dashboard__sep">-</span>
            <span class="data">{{ brewing.volume }}</span><span class="dashboard__unit">l</span>
          </p>
          <p class="chip dashboard__brewing-step">{{ $t(brewing.current_step ?? 'preparation') }}</p>
        </NuxtLink>
      </div>
    </section>
  </div>
</template>

<style scoped>
.dashboard {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.dashboard__metric {
  display: block;
  max-width: 14rem;
  padding: 1rem 1.25rem;
  transition: background-color 0.15s ease;
}

.dashboard__metric:hover {
  background-color: var(--color-panel-hi);
}

.dashboard__metric-value {
  font-size: var(--text-metric);
  color: var(--color-text);
}

.dashboard__metric-label {
  margin-top: 0.25rem;
  font-size: var(--text-label);
  color: var(--color-text-dim);
}

.dashboard__section {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.dashboard__heading {
  font-family: var(--font-display);
  text-transform: uppercase;
  letter-spacing: -0.02em;
  font-weight: 700;
  font-size: 1.125rem;
}

.dashboard__empty {
  color: var(--color-text-dim);
  font-size: var(--text-body);
  display: flex;
  flex-wrap: wrap;
  align-items: baseline;
  gap: 0.5rem;
}

.dashboard__empty-link {
  color: var(--color-signal);
}

.dashboard__ferment {
  padding: 1rem;
  border-left: 3px solid var(--brewing-color, var(--color-rule));
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.dashboard__ferment-name {
  font-weight: 600;
  color: var(--color-text);
}

.dashboard__ferment-name:hover {
  color: var(--color-text-dim);
}

.dashboard__ferment-recipe {
  font-size: var(--text-label);
  color: var(--color-text-dim);
  font-weight: 400;
}

.dashboard__ferment-row {
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
}

.dashboard__ferment-row-head {
  display: flex;
  justify-content: space-between;
  gap: 0.5rem;
  font-size: var(--text-label);
}

.dashboard__ferment-row-label {
  color: var(--color-text-dim);
}

.dashboard__ferment-row-meta {
  color: var(--color-text-dim);
  text-align: right;
}

.dashboard__ferment-row-meta--late {
  color: var(--color-ember);
}

.dashboard__progress-track {
  height: 0.375rem;
  border-radius: 9999px;
  background-color: var(--color-rule);
  overflow: hidden;
}

.dashboard__progress-bar {
  height: 100%;
  border-radius: 9999px;
  background-color: var(--color-malt);
}

.dashboard__grid {
  display: grid;
  gap: 0.75rem;
  grid-template-columns: 1fr;
}

@media (min-width: 640px) {
  .dashboard__grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (min-width: 1024px) {
  .dashboard__grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

.dashboard__brewing {
  display: block;
  padding: 1rem;
  border-left: 4px solid var(--brewing-color, var(--color-rule));
  transition: background-color 0.15s ease;
}

.dashboard__brewing:hover {
  background-color: var(--color-panel-hi);
}

.dashboard__brewing-name {
  font-weight: 600;
  color: var(--color-text);
}

.dashboard__brewing-recipe {
  font-size: var(--text-label);
  color: var(--color-text-dim);
}

.dashboard__brewing-meta {
  margin-top: 0.25rem;
  font-size: var(--text-label);
  color: var(--color-text-dim);
}

.dashboard__sep {
  color: var(--color-text-faint);
  margin-inline: 0.25rem;
}

.dashboard__unit {
  margin-left: 0.25rem;
}

.dashboard__brewing-step {
  margin-top: 0.5rem;
}
</style>
