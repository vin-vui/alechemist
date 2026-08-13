<script setup lang="ts">
import type { Brewing, BrewingStep } from '~/types/api'
import { boilRemainingMs, isStepLate, stepRemainingMinutes } from '~/utils/boil'

const props = defineProps<{ brewing: Brewing; steps: BrewingStep[]; readonly: boolean }>()
const emit = defineEmits<{ refresh: []; toggle: [step: BrewingStep]; done: [value: boolean] }>()

const nowMs = ref(Date.now())
let timer: ReturnType<typeof setInterval> | undefined
onMounted(() => { timer = setInterval(() => { nowMs.value = Date.now() }, 1000) })
onUnmounted(() => clearInterval(timer))

const started = computed(() => props.brewing.boil_start !== null)
const remainingMs = computed(() => boilRemainingMs(props.brewing.boil_start, props.brewing.boil_time, nowMs.value))
const elapsed = computed(() => remainingMs.value !== null && remainingMs.value <= 0)

watchEffect(() => emit('done', started.value && elapsed.value))

const modalOpen = ref(false)
const newBoilTime = ref(props.brewing.boil_time)

async function start() {
  await apiSend(`/api/brewings/${props.brewing.id}/start-boil`, 'POST')
  emit('refresh')
}

async function saveBoilTime() {
  if (newBoilTime.value > 0) {
    await apiSend(`/api/brewings/${props.brewing.id}`, 'PATCH', { boil_time: Math.trunc(newBoilTime.value) })
    emit('refresh')
  }
  modalOpen.value = false
}
</script>

<template>
  <div class="space-y-3">
    <div class="flex items-center justify-between rounded bg-white p-4 shadow">
      <p class="font-shangrila text-lg">{{ $t('Chrono') }} - {{ brewing.boil_time }} min</p>
      <div class="flex items-center gap-2">
        <template v-if="!started && !readonly">
          <button type="button" class="btn-yellow rounded" @click="start">{{ $t('Start') }}</button>
          <button type="button" class="rounded border px-3 py-1.5" @click="modalOpen = true; newBoilTime = brewing.boil_time">
            {{ $t('Change Time') }}
          </button>
        </template>
        <span v-else-if="started && !elapsed" class="info-label-yellow">
          {{ remainingMs !== null && remainingMs < 60_000
            ? $t('n_seconds', Math.max(0, Math.ceil(remainingMs / 1000)))
            : $t('n_minutes', Math.max(0, Math.ceil((remainingMs ?? 0) / 60_000))) }}
          {{ $t('left') }}
        </span>
        <span v-else-if="started && elapsed" class="info-label-yellow animate-pulse">{{ $t('Time elapsed') }}</span>
      </div>
    </div>

    <div v-if="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="modalOpen = false">
      <div class="w-full max-w-xs rounded bg-white p-4 shadow">
        <label class="block text-sm" for="boil-time">{{ $t('Change Time') }} (min)</label>
        <input id="boil-time" v-model.number="newBoilTime" type="number" min="1" class="mt-1 w-full rounded border-gray-300" />
        <div class="mt-3 flex justify-end gap-2">
          <button type="button" class="rounded border px-3 py-1.5" @click="modalOpen = false">{{ $t('Cancel') }}</button>
          <button type="button" class="btn-yellow rounded" @click="saveBoilTime">{{ $t('Save') }}</button>
        </div>
      </div>
    </div>

    <div
      v-for="step in steps"
      :key="step.id"
      class="flex items-center gap-3 rounded border-l-4 bg-white p-3 shadow"
      :class="isStepLate(brewing.boil_start, brewing.boil_time, step, nowMs)
        ? 'border-red-600'
        : step.status ? 'border-old-gold' : 'border-gray-200'"
    >
      <input
        type="checkbox"
        :checked="step.status"
        :disabled="readonly"
        class="size-5 rounded text-xanthous"
        @change="emit('toggle', step)"
      />
      <div class="flex-1">
        <p>{{ formatQuantity(step.quantity, step.unit) }} - {{ step.field }}</p>
        <p class="text-sm">
          <template v-if="step.type === 'Aroma'">
            <span class="info-label">{{ $t('flame-out') }} {{ $t('for') }} {{ $t('n_minutes', step.time ?? 0) }}</span>
          </template>
          <template v-else-if="started">
            <span v-if="isStepLate(brewing.boil_start, brewing.boil_time, step, nowMs)" class="font-semibold text-red-600">
              {{ $t('late') }}
              <span class="ml-1 inline-block size-2 animate-pulse rounded-full bg-red-600" />
            </span>
            <span v-else class="info-label">
              {{ $t('in') }} {{ $t('n_minutes', Math.max(0, stepRemainingMinutes(brewing.boil_start, brewing.boil_time, step, nowMs) ?? 0)) }}
            </span>
          </template>
          <template v-else>
            <span class="info-label">{{ $t('n_minutes', step.time ?? 0) }} {{ $t('before ends') }}</span>
          </template>
        </p>
      </div>
    </div>
  </div>
</template>
