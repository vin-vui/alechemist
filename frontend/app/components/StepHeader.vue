<script setup lang="ts">
import type { Brewing } from '~/types/api'

defineProps<{ brewing: Brewing; title: string }>()
</script>

<template>
  <div class="rounded bg-white p-4 shadow">
    <NuxtLink :to="`/recipes/${brewing.recipe_id}`" class="text-sm underline">&larr; {{ $t('Recipes') }}</NuxtLink>
    <div class="mt-1 flex items-center justify-between">
      <div>
        <h1 class="font-shangrila text-2xl">{{ brewing.name }}</h1>
        <p class="text-sm text-gray-500">
          {{ formatDate(brewing.date_start) }} - {{ brewing.volume }} l - {{ $t(title) }}
        </p>
      </div>
      <NuxtLink
        :to="`/recipes/${brewing.recipe_id}/brewings/${brewing.id}/note`"
        class="btn-yellow rounded-full"
        :title="$t('Note')"
      >
        {{ $t('Note') }}
      </NuxtLink>
    </div>
  </div>
</template>
