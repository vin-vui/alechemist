// Parité avec steps/preparation.blade.php : conversion g -> kg au-dela de 1000 g.
export function formatQuantity(quantity: number, unit: string): string {
  if (unit === 'g' && quantity >= 1000) {
    return `${quantity / 1000} kg`
  }
  return `${quantity} ${unit}`
}

export function formatDate(iso: string | null): string {
  if (!iso) return ''
  return new Intl.DateTimeFormat('fr-FR', { dateStyle: 'medium' }).format(new Date(iso))
}

// Chrono (docs/design-system.md #3) : MM:SS sous l'heure, H:MM:SS au-dela. Jamais negatif.
export function formatDuration(ms: number): string {
  const totalSeconds = Math.max(0, Math.round(ms / 1000))
  const hours = Math.floor(totalSeconds / 3600)
  const minutes = Math.floor((totalSeconds % 3600) / 60)
  const seconds = totalSeconds % 60
  const pad = (n: number) => String(n).padStart(2, '0')
  return hours > 0 ? `${hours}:${pad(minutes)}:${pad(seconds)}` : `${pad(minutes)}:${pad(seconds)}`
}
