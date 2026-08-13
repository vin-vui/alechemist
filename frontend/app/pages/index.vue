<script setup lang="ts">
import type { Dashboard } from '~/types/api'

const { data: dashboard, refresh } = await useAsyncData('dashboard', () => apiGet<Dashboard>('/api/dashboard'))
const now = new Date()
</script>

<template>
  <div v-if="dashboard" class="space-y-6">
    <NuxtLink to="/recipes" class="block max-w-xs rounded bg-rich-black p-4 text-anti-flash-white shadow hover:bg-licorice">
      <p class="font-shangrila text-3xl text-xanthous">{{ dashboard.recipe_count }}</p>
      <p>{{ $t('n_recipes', dashboard.recipe_count) }}</p>
    </NuxtLink>

    <section>
      <h2 class="mb-2 font-shangrila text-xl">{{ $t('n_fermentations', dashboard.ferment_brewings.length) }}</h2>
      <p v-if="dashboard.ferment_brewings.length === 0" class="text-gray-500">
        {{ $t('No fermentations in progress') }}
      </p>
      <div v-for="brewing in dashboard.ferment_brewings" :key="brewing.id" class="mb-4 rounded bg-white p-4 shadow">
        <NuxtLink :to="brewingPath(brewing)" class="font-semibold hover:underline">
          {{ brewing.name }} <span class="text-sm text-gray-500">({{ brewing.recipe?.name }})</span>
        </NuxtLink>
        <div
          v-for="view in fermentSchedule(brewing.ferment_start!, brewing.steps ?? [], now)"
          :key="view.step.id"
          class="mt-2"
        >
          <div class="flex justify-between text-sm">
            <span>{{ view.step.field }}</span>
            <span :class="{ 'text-red-600': view.late }">
              {{ view.late ? $t('late') : $t('n_days', view.daysLeft) }} - {{ formatDate(view.endsAt.toISOString()) }}
            </span>
          </div>
          <div class="h-2 rounded bg-gray-200">
            <div class="h-2 rounded bg-old-gold" :style="{ width: `${view.progress}%` }" />
          </div>
        </div>
      </div>
    </section>

    <section>
      <h2 class="mb-2 font-shangrila text-xl">{{ $t('Current step') }}</h2>
      <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
        <NuxtLink
          v-for="brewing in dashboard.in_progress_brewings"
          :key="brewing.id"
          :to="brewingPath(brewing)"
          class="rounded bg-white p-4 shadow hover:ring-2 hover:ring-xanthous"
        >
          <p class="font-semibold">{{ brewing.name }}</p>
          <p class="text-sm text-gray-500">{{ brewing.recipe?.name }}</p>
          <p class="text-sm">{{ formatDate(brewing.date_start) }} - {{ brewing.volume }} l</p>
          <p class="mt-1"><span class="info-label-yellow">{{ $t(brewing.current_step ?? 'preparation') }}</span></p>
        </NuxtLink>
      </div>
    </section>
  </div>
</template>
