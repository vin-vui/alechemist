<script setup lang="ts">
import type { Brewing, Recipe, StepType } from '~/types/api'

const route = useRoute()
const recipeId = Number(route.params.recipe)
const brewingId = Number(route.params.brewing)

const { data: brewing } = await useAsyncData(`brewing-${brewingId}`, () => apiGet<Brewing>(`/api/brewings/${brewingId}`))
const { data: recipe } = await useAsyncData(`recipe-${recipeId}`, () => apiGet<Recipe>(`/api/recipes/${recipeId}`))

if (brewing.value && brewing.value.recipe_id !== recipeId) {
  throw createError({ statusCode: 404 })
}

const TABLES: { title: string; types: StepType[] }[] = [
  { title: 'Boil', types: ['Boil', 'Aroma'] },
  { title: 'Yeasts', types: ['Yeast', 'Dry Hop'] },
  { title: 'Ferment', types: ['Primary', 'Secondary', 'Tertiary', 'Bottle'] },
]

function brewingSteps(types: StepType[]) {
  return (brewing.value?.steps ?? []).filter((s) => types.includes(s.type))
}

// Parite web assumee : comparaison par position dans le meme groupe de types
// (les brewing_steps sont crees dans l'ordre des steps de la recette).
function recipeTimeAt(types: StepType[], index: number): number | null {
  const steps = (recipe.value?.steps ?? []).filter((s) => types.includes(s.type))
  return steps[index]?.time ?? null
}

async function destroyBrewing() {
  if (!confirm(useNuxtApp().$i18n.t('Are you sure?'))) return
  await apiSend(`/api/brewings/${brewingId}`, 'DELETE')
  await navigateTo(`/recipes/${recipeId}/brewings`)
}
</script>

<template>
  <div v-if="brewing" class="space-y-4">
    <NuxtLink :to="`/recipes/${recipeId}/brewings`" class="text-sm underline">&larr; {{ $t('Brewing list') }}</NuxtLink>
    <div class="rounded bg-white p-4 shadow">
      <h1 class="font-shangrila text-2xl">{{ brewing.name }} <span class="info-label text-sm">{{ $t('finished') }}</span></h1>
      <div class="mt-2 grid grid-cols-1 gap-1 text-sm sm:grid-cols-3">
        <p>{{ $t('Boil') }} : {{ formatDate(brewing.boil_start) }}</p>
        <p>{{ $t('Ferment') }} : {{ formatDate(brewing.ferment_start) }}</p>
        <p>{{ $t('End') }} : {{ formatDate(brewing.ferment_end) }}</p>
      </div>
    </div>

    <section v-for="table in TABLES" :key="table.title">
      <template v-if="brewingSteps(table.types).length > 0">
        <h2 class="mb-1 font-shangrila text-lg">{{ $t(table.title) }}</h2>
        <div class="overflow-x-auto rounded bg-white shadow">
          <table class="w-full text-left text-sm">
            <thead class="bg-rich-black text-anti-flash-white">
              <tr>
                <th class="px-3 py-2">{{ $t('Name') }}</th>
                <th class="px-3 py-2">{{ $t('Quantity') }}</th>
                <th class="px-3 py-2">{{ table.title === 'Ferment' ? $t('Days') : $t('Minutes') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(step, index) in brewingSteps(table.types)" :key="step.id" class="border-t">
                <td class="px-3 py-2">{{ step.field }}</td>
                <td class="px-3 py-2">{{ formatQuantity(step.quantity, step.unit) }}</td>
                <td class="px-3 py-2" :class="{ 'font-bold text-tawny': step.time !== recipeTimeAt(table.types, index) }">
                  {{ table.title === 'Ferment' ? minutesToDays(step.time) : (step.time ?? '') }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </template>
    </section>

    <div class="rounded bg-white p-4 shadow">
      <h2 class="mb-1 font-shangrila text-lg">{{ $t('Note') }}</h2>
      <p class="whitespace-pre-wrap text-sm">{{ brewing.note || '-' }}</p>
    </div>

    <button type="button" class="text-sm text-red-600 underline" @click="destroyBrewing">{{ $t('Delete') }}</button>
  </div>
</template>
