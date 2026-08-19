/*
 * Les notes d'une recette, rassemblees depuis ses brassins.
 *
 * Une note vit sur le brassin, et ne se lisait que depuis le brassin : retrouver ce
 * qu'on avait note la fois d'avant demandait de passer par la liste des brassins puis
 * d'en ouvrir un. La fiche recette les montre desormais toutes, ce qui n'a besoin que
 * de ce tri : le journal se lit du dernier brassin au premier.
 */
import type { Brewing } from '~/types/api'

/** Une note vide ou faite d'espaces n'est pas une note : elle ne merite pas de ligne. */
function hasNote(brewing: Brewing): boolean {
  return (brewing.note ?? '').trim().length > 0
}

export function brewingsWithNotes(brewings: Brewing[] | null | undefined): Brewing[] {
  return (brewings ?? [])
    .filter(hasNote)
    // Copie avant tri : la liste recue est celle du useAsyncData de la page, la
    // reordonner sur place changerait aussi l'ordre de la liste des brassins.
    .slice()
    // date_start est en Y-m-d, donc l'ordre lexicographique est l'ordre chronologique.
    // Deux brassins peuvent partager un jour : le plus recemment cree passe devant.
    .sort((a, b) => (a.date_start === b.date_start ? b.id - a.id : a.date_start < b.date_start ? 1 : -1))
}
