# Roadmap de migration Alechemist — Laravel 13 + Nuxt 4

**Date :** 2026-08-11
**Source :** audit du 2026-08-11 (session Claude Code)

## Objectif final

Remplacer le frontend Livewire/Jetstream par une application Nuxt 4, adossée à un backend Laravel 13 API-only, sans régression fonctionnelle (PWA de suivi de brassage, auth 2FA incluse).

## État de départ (constaté)

- Laravel 11.7 (ancienne structure bootstrap style L10 : `app/Http/Kernel.php`, `app/Exceptions/Handler.php`)
- Jetstream 5.1 + Fortify + Sanctum, Livewire 3.4.12
- 13 composants Livewire full-page, 5 modèles, 12 routes web, 19 vues Blade custom
- Front : Vite 4, Tailwind 3, PWA artisanale (`public/manifest.json` + `pwabuilder-sw.js`)
- Tests : 100 % stock Jetstream, zéro test métier
- Failles : IDOR généralisé (aucune policy), upload public jamais nettoyé, import XML sans transaction

## Architecture cible

- **Backend** : Laravel 13 (PHP 8.3+), API REST sous `routes/api.php`, Sanctum en mode SPA (cookies), Fortify headless pour login / 2FA / profil. Jetstream retiré.
- **Frontend** : Nuxt 4 (Nuxt 3 est EOL depuis juillet 2026), `nuxt-auth-sanctum`, Tailwind 4, `@vite-pwa/nuxt`, `@nuxtjs/i18n` (reprise de `lang/fr.json` / `lang/en.json`).
- Les 5 pages d'étapes quasi identiques (preparation/mash/boil/yeast/ferment) deviennent **une seule page dynamique** `pages/recipes/[recipe]/brewings/[brewing]/[step].vue`.
- Le chrono d'ébullition (`wire:poll.s`, ~5 000 requêtes serveur par brassin) devient un timer client.
- Schéma de base de données **inchangé** — aucune migration de données.

## Phases

Chaque phase laisse l'application fonctionnelle et se termine par une suite de tests verte. Un plan détaillé est écrit **au début de chaque phase** (les détails dépendent de l'atterrissage de la précédente).

### Phase 1 — Sécurisation (plan détaillé : `2026-08-11-phase-1-securisation.md`)

Policies + autorisation dans les `mount()`, garde-fous sur les actions Livewire à paramètre client, import BeerXML sans persistance fichier + transaction + gestion d'erreur, scoping `HomeController`, suppression du code mort. Prérequis de tout le reste : les policies écrites ici protégeront aussi l'API de la phase 3.

**Sortie :** aucune donnée d'un utilisateur accessible/modifiable par un autre ; suite verte incluant les nouveaux tests d'autorisation et d'import.

**✅ Réalisée le 2026-08-11** (mergée dans `main`). **Action de déploiement requise : purger `public/files/` sur le serveur de prod** — l'ancien code y a accumulé les XML uploadés, publiquement accessibles ; plus rien n'y écrit désormais (vérifié : ni photos de profil Jetstream ni autre usage du disque `public`). Prévoir aussi `php artisan optimize:clear` au déploiement. Découverte à normaliser en phase 2/3 : `config/filesystems.php` route le disque `public` vers `public_path('files')` (hack pré-existant, désormais sans consommateur).

### Phase 2 — Upgrade Laravel 11 → 12 → 13, PHP 8.3+

- Corrections préalables déjà faites en phase 1 (propriétés dynamiques, signature `createStep`).
- `laravel/framework` 11 → 12 → 13 (L12 et L13 ont très peu de breaking changes), PHPUnit 10 → 11+, `composer.json` `php: ^8.3`.
- Adoption de la nouvelle structure `bootstrap/app.php` (suppression des Kernels et du Handler).
- **Point de décision en début de phase :** vérifier la compatibilité de Jetstream avec L13. Si Jetstream bloque le saut vers 13, s'arrêter à L12 (supporté par Jetstream 5) et faire le saut 12 → 13 en phase 3 après le retrait de Jetstream.

**Sortie :** suite verte sur la version de Laravel la plus récente que permet Jetstream (12 ou 13).

**✅ Réalisée le 2026-08-12** (plan détaillé : `2026-08-12-phase-2-upgrade-laravel.md`). Le point de décision est tranché : Jetstream 5.5 supporte Laravel 13 — l'app est directement en **Laravel 13 / PHP ^8.3 / PHPUnit 12**, nouvelle structure `bootstrap/app.php` adoptée (kernels, Handler et middleware stock supprimés), CI GitHub Actions en place (la roadmap disait « GitLab CI » mais `origin` est GitHub), disque `public` normalisé, 0 advisory `composer audit` (46 au départ). **Actions de déploiement requises : PHP ≥ 8.3 sur le serveur AVANT déploiement**, puis `composer install --no-dev` et `php artisan optimize:clear`. `config/session.php` fige `serialization => php` (sessions préservées) — à passer à `json` en phase 3/4. La purge de `public/files/` en prod (phase 1) reste à faire si pas déjà faite.

### Phase 3 — API REST + Fortify headless

- Controllers API (`RecipeController`, `BrewingController`, `BrewingStepController`, `RecipeImportController`) + Form Requests + API Resources, autorisés par les policies de la phase 1.
- Extraction de l'import BeerXML dans un service (`app/Services/BeerXmlImporter.php`) partagé entre le contrôleur web existant et l'API.
- Sanctum SPA : config CORS + `SANCTUM_STATEFUL_DOMAINS`, Fortify headless (login, logout, 2FA challenge, profil, reset password).
- Tests Feature sur chaque endpoint (le vide de tests métier se comble ici).
- Les routes web Livewire restent en place — les deux frontends coexistent jusqu'à la bascule.
- Si Jetstream a bloqué L13 en phase 2 : retirer Jetstream ici (remplacé par Fortify) puis terminer l'upgrade 12 → 13.

**Sortie :** API complète couvrant tous les cas d'usage des 12 routes web, testée, consommable par un client externe.

### Phase 4 — Frontend Nuxt 4 + bascule

- Projet Nuxt 4 (dépôt ou dossier `frontend/` — décision en début de phase), auth via `nuxt-auth-sanctum` (login + 2FA + reset), Tailwind 4.
- Pages : home (dashboard), recettes (liste/détail), brassins (liste/création/complété), page d'étape dynamique unique, import BeerXML, note.
- PWA (`@vite-pwa/nuxt`) et i18n (reprise des clés `lang/*.json`).
- Chrono d'ébullition en timer client (plus de polling serveur).
- Bascule (reverse proxy ou domaine dédié), puis décommission : suppression de `app/Livewire/`, des vues Blade custom, de Jetstream/Livewire dans composer.json, des routes web remplacées.

**Sortie :** Nuxt en production, Livewire supprimé du dépôt.

## Risques identifiés

| Risque | Phase | Mitigation |
|---|---|---|
| Compat Jetstream ↔ Laravel 13 | 2 | Résolu le 2026-08-12 : Jetstream 5.5.3 supporte L13 |
| Réimplémentation 2FA côté Nuxt (flux challenge Fortify) | 4 | Fortify headless expose les endpoints standard ; `nuxt-auth-sanctum` documente le flux |
| Comportement offline PWA (service worker artisanal actuel) | 4 | `@vite-pwa/nuxt` remplace `pwabuilder-sw.js` ; recette de test manuel avant bascule |
| Import BeerXML : variantes de structure XML non couvertes (fermentable unique, multi-recettes) | 1/3 | Tests fixtures en phase 1, durcissement du service en phase 3 |
| Pas de CI | transverse | Ajouter un pipeline GitLab CI (phpunit) dès la fin de phase 1 |
