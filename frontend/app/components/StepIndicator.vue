<script setup lang="ts">
import type { Brewing } from '~/types/api'
import { STEP_ORDER } from '~/utils/steps'

const props = defineProps<{ brewing: Brewing; active: string }>()

const currentIndex = computed(() =>
  STEP_ORDER.indexOf(props.brewing.current_step ?? 'preparation'))
</script>

<template>
  <nav class="flex items-center gap-1 overflow-x-auto py-2">
    <NuxtLink
      v-for="(slug, index) in STEP_ORDER.slice(0, 5)"
      :key="slug"
      :to="`/recipes/${brewing.recipe_id}/brewings/${brewing.id}/${slug}`"
      class="rounded-full px-3 py-1 text-sm whitespace-nowrap"
      :class="{
        'bg-xanthous font-semibold': slug === active,
        'bg-old-gold/50': slug !== active && index < currentIndex,
        'bg-gray-200': slug !== active && index >= currentIndex,
      }"
    >
      {{ $t(slug) }}
    </NuxtLink>
  </nav>
</template>
