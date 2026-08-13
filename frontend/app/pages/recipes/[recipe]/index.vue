<script setup lang="ts">
import type { Recipe, Step, StepType } from '~/types/api'
import { RECIPE_TABLES, formatStepTime, timeUnitFor } from '~/utils/steps'

const route = useRoute()
const recipeId = Number(route.params.recipe)
const { data: recipe, error, refresh } = await useAsyncData(`recipe-${recipeId}`, () => apiGet<Recipe>(`/api/recipes/${recipeId}`))
if (error.value) {
  throw createError({ statusCode: error.value.statusCode ?? 500, statusMessage: error.value.statusMessage ?? error.value.message })
}

function stepsFor(types: StepType[]): Step[] {
  return (recipe.value?.steps ?? []).filter((s) => types.includes(s.type))
}

const showMore = ref(false)
const editingDensity = ref(false)
const densityInput = ref<string>('')
const mutationError = ref(false)

function startEditDensity() {
  densityInput.value = recipe.value?.before_boil_density?.toString() ?? ''
  editingDensity.value = true
}

async function saveDensity() {
  const trimmed = densityInput.value.trim()
  try {
    await apiSend(`/api/recipes/${recipeId}`, 'PATCH', { before_boil_density: trimmed === '' ? null : Number(trimmed) })
    editingDensity.value = false
    mutationError.value = false
    await refresh()
  } catch {
    mutationError.value = true
  }
}

async function destroyRecipe() {
  if (!confirm(useNuxtApp().$i18n.t('Are you sure?'))) return
  try {
    await apiSend(`/api/recipes/${recipeId}`, 'DELETE')
    await navigateTo('/recipes')
  } catch {
    mutationError.value = true
  }
}
</script>

<template>
  <div v-if="recipe" class="space-y-4">
    <div class="flex items-center justify-between">
      <NuxtLink to="/recipes" class="text-sm underline">&larr; {{ $t('Recipes') }}</NuxtLink>
      <button type="button" class="text-sm text-red-600 underline" @click="destroyRecipe">{{ $t('Delete') }}</button>
    </div>

    <p v-if="mutationError" class="rounded bg-red-50 p-2 text-sm text-red-600">
      {{ $t('Whoops! Something went wrong.') }}
    </p>
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
                <th class="px-3 py-2">{{ $t(table.unit === 'days' ? 'Days' : 'Minutes') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="step in stepsFor(table.types)" :key="step.id" class="border-t">
                <td class="px-3 py-2">{{ step.field }}</td>
                <td class="px-3 py-2">{{ formatQuantity(step.quantity, step.unit) }}</td>
                <td class="px-3 py-2">
                  {{ formatStepTime(step.time, timeUnitFor(table, step.type)) }}
                  <span v-if="step.time != null && timeUnitFor(table, step.type) !== (table.unit ?? 'minutes')" class="text-xs text-gray-400">
                    {{ $t(timeUnitFor(table, step.type) === 'days' ? 'Days' : 'Minutes') }}
                  </span>
                </td>
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
