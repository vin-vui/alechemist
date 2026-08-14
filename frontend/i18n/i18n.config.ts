// La regle de pluriel par defaut de vue-i18n est anglaise : zero prend le pluriel.
// En francais, zero prend le singulier. Sans cette regle, l'app affichait
// « 0 recettes » et « dans 0 minutes ».
export default defineI18nConfig(() => ({
  pluralRules: {
    fr: (choice: number, choicesLength: number) => {
      if (choicesLength < 2) return 0
      return choice <= 1 ? 0 : 1
    },
  },
}))
