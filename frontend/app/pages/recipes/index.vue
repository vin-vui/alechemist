<script setup lang="ts">
import type { Recipe } from '~/types/api'

const { data: recipes, error } = await useAsyncData('recipes', () => apiGet<Recipe[]>('/api/recipes'))
if (error.value) {
  throw createError({ statusCode: error.value.statusCode ?? 500, statusMessage: error.value.statusMessage ?? error.value.message })
}
</script>

<template>
  <div>
    <h1 class="mb-4 font-shangrila text-2xl">{{ $t('Recipes') }}</h1>
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
      <NuxtLink
        v-for="recipe in recipes ?? []"
        :key="recipe.id"
        :to="`/recipes/${recipe.id}`"
        class="rounded bg-white p-4 shadow hover:ring-2 hover:ring-xanthous"
      >
        <div class="flex items-center gap-3">
          <svg class="size-8 shrink-0" viewBox="0 0 24 24" :style="{ color: ebcToHex(recipe.color) }" fill="currentColor">
            <path d="M5 3h14v4a7 7 0 0 1-4 6.3V19h3v2H6v-2h3v-5.7A7 7 0 0 1 5 7V3z" />
          </svg>
          <div>
            <p class="font-semibold">{{ recipe.name }}</p>
            <p class="text-sm text-gray-500">{{ recipe.type }} - {{ $t('n_brewings', recipe.brewings_count ?? 0) }}</p>
          </div>
        </div>
      </NuxtLink>
    </div>
    <NuxtLink to="/recipes/import" class="btn-yellow fixed bottom-6 right-6 rounded-full">
      {{ $t('Import') }}
    </NuxtLink>
  </div>
</template>
