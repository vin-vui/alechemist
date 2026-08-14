/*
 * Les nombres etaient rendus par la conversion JS par defaut : « 5.6 % », « 1.052 »,
 * « 2.5 kg », point decimal anglais, dans une interface francaise. Et formatDate
 * etait fige en fr-FR alors qu'une locale `en` existe. Tout passe maintenant par
 * la locale active, posee depuis app.vue au montage et a chaque changement de langue.
 */
const LOCALE_TAGS: Record<string, string> = { fr: 'fr-FR', en: 'en-GB' }

let currentTag = LOCALE_TAGS.fr!

/** Appele depuis app.vue a chaque changement de langue. */
export function setFormatLocale(code: string): void {
  currentTag = LOCALE_TAGS[code] ?? LOCALE_TAGS.fr!
}

/**
 * Accepte aussi une chaine : l'API sérialise les colonnes décimales de MySQL en
 * chaines ("5.60", "45.3"), et c'est ce qui atterrissait tel quel dans la page.
 */
export type Numeric = number | string | null | undefined

function toNumber(value: Numeric): number | null {
  if (value == null || value === '') return null
  const parsed = typeof value === 'number' ? value : Number(value)
  return Number.isFinite(parsed) ? parsed : null
}

export function formatNumber(value: Numeric, options: Intl.NumberFormatOptions = {}): string {
  const parsed = toNumber(value)
  if (parsed == null) return ''
  return new Intl.NumberFormat(currentTag, { maximumFractionDigits: 3, ...options }).format(parsed)
}

/** Densite : toujours trois decimales, sinon « 1,052 » cotoie « 1,01 ». */
export function formatDensity(value: Numeric): string {
  return formatNumber(value, { minimumFractionDigits: 3, maximumFractionDigits: 3 })
}

export function formatPercent(value: Numeric): string {
  const formatted = formatNumber(value)
  return formatted === '' ? '' : `${formatted} %`
}

/**
 * Lit un décimal saisi à la main, dans les deux notations.
 *
 * L'app affiche « 1,052 » : un utilisateur francophone tape une virgule, et
 * `Number('1,048')` vaut NaN. Rend `null` pour un champ vide, `undefined` pour une
 * saisie qui n'est pas un nombre, ce que l'appelant doit distinguer (effacer la
 * valeur, ou refuser la saisie).
 */
export function parseDecimalInput(value: string | number | null | undefined): number | null | undefined {
  if (typeof value === 'number') return Number.isFinite(value) ? value : undefined
  const text = (value ?? '').toString().trim().replace(',', '.')
  if (text === '') return null
  const parsed = Number(text)
  return Number.isFinite(parsed) ? parsed : undefined
}

// Parité avec steps/preparation.blade.php : conversion g -> kg au-dela de 1000 g.
export function formatQuantity(quantity: Numeric, unit: string): string {
  const parsed = toNumber(quantity)
  if (parsed == null) return ''
  if (unit === 'g' && parsed >= 1000) {
    return `${formatNumber(parsed / 1000)} kg`
  }
  return `${formatNumber(parsed)} ${unit}`
}

export function formatDate(iso: string | null): string {
  if (!iso) return ''
  return new Intl.DateTimeFormat(currentTag, { dateStyle: 'medium' }).format(new Date(iso))
}

// Chrono (docs/design-system.md #3) : MM:SS sous l'heure, H:MM:SS au-dela. Jamais negatif.
// Pas de formatNumber ici : le chrono est un affichage a chasse fixe, pas un nombre
// localise (« 01:05 » ne devient pas « 1,05 »).
export function formatDuration(ms: number): string {
  const totalSeconds = Math.max(0, Math.round(ms / 1000))
  const hours = Math.floor(totalSeconds / 3600)
  const minutes = Math.floor((totalSeconds % 3600) / 60)
  const seconds = totalSeconds % 60
  const pad = (n: number) => String(n).padStart(2, '0')
  return hours > 0 ? `${hours}:${pad(minutes)}:${pad(seconds)}` : `${pad(minutes)}:${pad(seconds)}`
}
