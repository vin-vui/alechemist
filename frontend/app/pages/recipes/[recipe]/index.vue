<script setup lang="ts">
import type { Recipe, Step, StepType } from '~/types/api'

const route = useRoute()
const recipeId = Number(route.params.recipe)
const { data: recipe, refresh } = await useAsyncData(`recipe-${recipeId}`, () => apiGet<Recipe>(`/api/recipes/${recipeId}`))

const RECIPE_TABLES: { title: string; types: StepType[] }[] = [
  { title: 'Preparation', types: ['Preparation'] },
  { title: 'Mash', types: ['Mash'] },
  { title: 'Boil', types: ['Boil', 'Aroma'] },
  { title: 'Yeasts', types: ['Yeast', 'Dry Hop'] },
  { title: 'Ferment', types: ['Primary', 'Secondary', 'Tertiary', 'Bottle'] },
]

function stepsFor(types: StepType[]): Step[] {
  return (recipe.value?.steps ?? []).filter((s) => types.includes(s.type))
}

const showMore = ref(false)
const editingDensity = ref(false)
const densityInput = ref<string>('')

function startEditDensity() {
  densityInput.value = recipe.value?.before_boil_density?.toString() ?? ''
  editingDensity.value = true
}

async function saveDensity() {
  await apiSend(`/api/recipes/${recipeId}`, 'PATCH', { before_boil_density: Number(densityInput.value) })
  editingDensity.value = false
  await refresh()
}

async function destroyRecipe() {
  if (!confirm(useNuxtApp().$i18n.t('Are you sure?'))) return
  await apiSend(`/api/recipes/${recipeId}`, 'DELETE')
  await navigateTo('/recipes')
}
</script>

<template>
  <div v-if="recipe" class="space-y-4">
    <div class="flex items-center justify-between">
      <NuxtLink to="/recipes" class="text-sm underline">&larr; {{ $t('Recipes') }}</NuxtLink>
      <button type="button" class="text-sm text-red-600 underline" @click="destroyRecipe">{{ $t('Delete') }}</button>
    </div>
    <div class="rounded bg-white p-4 shadow">
      <div class="flex items-center gap-3">
        <svg class="size-10 shrink-0" viewBox="0 0 24 24" :style="{ color: ebcToHex(recipe.color) }" fill="currentColor">
          <path d="M5 3h14v4a7 7 0 0 1-4 6.3V19h3v2H6v-2h3v-5.7A7 7 0 0 1 5 7V3z" />
        </svg>
        <div>
          <h1 class="font-shangrila text-2xl">{{ recipe.name }}</h1>
          <p class="text-sm text-gray-500">{{ recipe.type }} - {{ recipe.alcohol }} % - {{ recipe.volume }} l</p>
        </div>
      </div>

      <div class="mt-3 flex flex-wrap gap-2 text-sm">
        <span class="info-label">{{ $t('Method') }} : {{ recipe.method }}</span>
        <span class="info-label">IBU {{ recipe.bitterness }}</span>
        <span class="info-label">EBC {{ recipe.color }}</span>
        <span class="info-label">{{ recipe.boil_time }} min</span>
      </div>

      <div class="mt-3 text-sm">
        <span class="mr-2">{{ $t('Before Boil Density') }} :</span>
        <template v-if="!editingDensity">
          <span :class="recipe.before_boil_density == null ? 'rounded px-2 py-0.5 ring-2 ring-red-500' : 'info-label'">
            {{ recipe.before_boil_density ?? '-' }}
          </span>
          <button type="button" class="ml-2 underline" @click="startEditDensity">{{ $t('Edit') }}</button>
        </template>
        <template v-else>
          <input v-model="densityInput" type="number" step="0.001" class="w-24 rounded border-gray-300 py-0.5" />
          <button type="button" class="ml-2 underline" @click="saveDensity">{{ $t('Save') }}</button>
        </template>
      </div>

      <button type="button" class="mt-3 text-sm underline" @click="showMore = !showMore">
        {{ showMore ? $t('Less') : $t('More') }}
      </button>
      <dl v-if="showMore" class="mt-2 grid grid-cols-2 gap-2 text-sm sm:grid-cols-3">
        <div><dt class="text-gray-500">{{ $t('Initial Density') }}</dt><dd>{{ recipe.initial_density }}</dd></div>
        <div><dt class="text-gray-500">{{ $t('Final Density') }}</dt><dd>{{ recipe.final_density }}</dd></div>
        <div><dt class="text-gray-500">{{ $t('Efficiency') }}</dt><dd>{{ recipe.efficiency }}</dd></div>
        <div><dt class="text-gray-500">{{ $t('Carbonation') }}</dt><dd>{{ recipe.carbonation }}</dd></div>
        <div><dt class="text-gray-500">{{ $t('Ferment') }}</dt><dd>{{ recipe.ferment }}</dd></div>
      </dl>
    </div>

    <section v-for="table in RECIPE_TABLES" :key="table.title">
      <template v-if="stepsFor(table.types).length > 0">
        <h2 class="mb-1 font-shangrila text-lg">{{ $t(table.title) }}</h2>
        <div class="overflow-x-auto rounded bg-white shadow">
          <table class="w-full text-left text-sm">
            <thead class="bg-rich-black text-anti-flash-white">
              <tr>
                <th class="px-3 py-2">{{ $t('Name') }}</th>
                <th class="px-3 py-2">{{ $t('Quantity') }}</th>
                <th class="px-3 py-2">{{ $t('Minutes') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="step in stepsFor(table.types)" :key="step.id" class="border-t">
                <td class="px-3 py-2">{{ step.field }}</td>
                <td class="px-3 py-2">{{ formatQuantity(step.quantity, step.unit) }}</td>
                <td class="px-3 py-2">{{ step.time ?? '' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </template>
    </section>

    <NuxtLink :to="`/recipes/${recipe.id}/brewings`" class="btn-yellow inline-flex rounded">
      {{ $t('Brewing list') }}
    </NuxtLink>
  </div>
</template>
