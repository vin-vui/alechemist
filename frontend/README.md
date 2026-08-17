# AleChemist, interface

SPA Nuxt 4 en génération statique (`ssr: false`). Le contexte général du projet, l'architecture et le déploiement sont décrits dans le [README à la racine](../README.md).

## Développement

```bash
npm install
npm run dev          # http://localhost:3000
```

L'API doit tourner en parallèle sur `http://localhost:8000`, via un `php artisan serve` à la racine du dépôt. C'est la valeur par défaut de `sanctum.baseUrl` dans [nuxt.config.ts](nuxt.config.ts), et l'authentification se fait par cookie de session, donc les deux serveurs doivent être joignables.

## Tests

```bash
npm run test:run     # Vitest sur les modules purs
npm run typecheck    # vue-tsc
```

## Build

```bash
NUXT_PUBLIC_SANCTUM_BASE_URL=https://<domaine> npm run generate
```

Le résultat est écrit dans `.output/public`, qui sert de racine web en production. L'URL de l'API est figée au moment du build, donc tout changement de domaine impose un nouveau build, pas seulement un nouveau déploiement.
