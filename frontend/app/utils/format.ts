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
