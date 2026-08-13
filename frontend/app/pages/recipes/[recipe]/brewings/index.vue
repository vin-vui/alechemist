<script setup lang="ts">
import type { Brewing } from '~/types/api'

const route = useRoute()
const recipeId = Number(route.params.recipe)
const { data: brewings } = await useAsyncData(`brewings-${recipeId}`, () =>
  apiGet<Brewing[]>(`/api/recipes/${recipeId}/brewings`))
</script>

<template>
  <div>
    <div class="mb-4 flex items-center justify-between">
      <h1 class="font-shangrila text-2xl">{{ $t('Brewing list') }}</h1>
      <NuxtLink :to="`/recipes/${recipeId}/brewings/new`" class="btn-yellow rounded">{{ $t('New Brewing') }}</NuxtLink>
    </div>
    <NuxtLink :to="`/recipes/${recipeId}`" class="text-sm underline">&larr; {{ $t('Recipes') }}</NuxtLink>
    <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
      <NuxtLink
        v-for="brewing in brewings ?? []"
        :key="brewing.id"
        :to="brewingPath(brewing)"
        class="rounded bg-white p-4 shadow hover:ring-2 hover:ring-xanthous"
      >
        <p class="font-semibold">{{ brewing.name }}</p>
        <p class="text-sm text-gray-500">{{ formatDate(brewing.date_start) }} - {{ brewing.volume }} l</p>
        <p class="mt-1">
          <span :class="brewing.current_step === 'completed' ? 'info-label' : 'info-label-yellow'">
            {{ $t(brewing.current_step ?? 'preparation') }}
          </span>
        </p>
      </NuxtLink>
    </div>
  </div>
</template>
