<script setup lang="ts">
/*
 * La note d'un brassin, en dialogue plutot qu'en ecran.
 *
 * Elle occupait une page entiere : on quittait l'etape en cours pour ecrire trois
 * mots, et il fallait ensuite un chemin de retour pour revenir a son brassin. Le
 * dialogue se pose sur l'ecran, qui reste visible derriere, et se ferme sur place.
 */
import type { Brewing } from '~/types/api'

const props = defineProps<{ brewing: Brewing; open: boolean }>()
const emit = defineEmits<{ close: []; saved: [note: string] }>()

const note = ref(props.brewing.note ?? '')
const pending = ref(false)
const failed = ref(false)
const field = ref<HTMLTextAreaElement | null>(null)

watch(
  () => props.open,
  async (open) => {
    if (!open) return
    // A chaque ouverture, le dialogue repart de la note enregistree et non du
    // brouillon abandonne a l'ouverture precedente.
    note.value = props.brewing.note ?? ''
    failed.value = false
    await nextTick()
    field.value?.focus()
  },
)

async function save() {
  pending.value = true
  failed.value = false
  try {
    await apiSend(`/api/brewings/${props.brewing.id}`, 'PATCH', { note: note.value })
    emit('saved', note.value)
    emit('close')
  } catch {
    failed.value = true
  } finally {
    pending.value = false
  }
}
</script>

<template>
  <div
    v-if="open"
    class="note-dialog"
    role="dialog"
    aria-modal="true"
    :aria-label="$t('Note')"
    @click.self="emit('close')"
    @keydown.esc="emit('close')"
  >
    <div class="panel note-dialog__card">
      <h2 class="note-dialog__title">{{ $t('Note') }}</h2>
      <p class="note-dialog__brewing">{{ brewing.name }}</p>
      <p v-if="failed" class="banner-error">{{ $t('Note not saved') }}</p>
      <textarea
        ref="field"
        v-model="note"
        rows="8"
        class="field__input field__textarea"
        :aria-label="$t('Note')"
        :placeholder="$t('Note placeholder')"
      />
      <div class="note-dialog__actions">
        <button type="button" class="btn btn-secondary" @click="emit('close')">
          {{ $t('Cancel') }}
        </button>
        <button type="button" class="btn btn-primary" :disabled="pending" @click="save">
          {{ $t('Save the note') }}
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.note-dialog {
  position: fixed;
  inset: 0;
  z-index: 50;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  background-color: rgb(14 14 20 / 0.8);
}

.note-dialog__card {
  width: 100%;
  max-width: 32rem;
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.note-dialog__title {
  font-family: var(--font-display);
  text-transform: uppercase;
  letter-spacing: -0.02em;
  font-weight: 700;
  font-size: 1.125rem;
}

/* Le nom du brassin : le dialogue peut s'ouvrir depuis deux ecrans, il dit de quoi
   il parle. */
.note-dialog__brewing {
  color: var(--color-text-dim);
  font-size: var(--text-label);
}

.note-dialog__actions {
  margin-top: 0.5rem;
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
}

.note-dialog__actions > * {
  flex: 1 1 8rem;
}
</style>
