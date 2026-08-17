<p align="center">
  <img src="frontend/public/assets/logo/alechemist-192.png" width="112" alt="AleChemist">
</p>

<h1 align="center">AleChemist</h1>

<p align="center">Suivi de brassage amateur, de l'import de la recette jusqu'à la mise en bouteille.</p>

<p align="center">
  <a href="https://forge.laravel.com/vincent-vuillemin/amiable-storm-8qs/3340705"><img src="https://img.shields.io/endpoint?url=https%3A%2F%2Fforge.laravel.com%2Fsite-badges%2F05da8d71-4e4e-4624-8d40-636f63905f22%3Fdate%3D1%26label%3D1&style=flat-square" alt="Laravel Forge Site Deployment Status"></a>
  <a href="https://github.com/vin-vui/alechemist/actions/workflows/ci.yml"><img src="https://github.com/vin-vui/alechemist/actions/workflows/ci.yml/badge.svg" alt="Tests"></a>
  <img src="https://img.shields.io/badge/PHP-8.5-777BB4?style=flat-square&logo=php&logoColor=white" alt="PHP 8.5">
  <img src="https://img.shields.io/badge/Laravel-13-FF2D20?style=flat-square&logo=laravel&logoColor=white" alt="Laravel 13">
  <img src="https://img.shields.io/badge/Nuxt-4-00DC82?style=flat-square&logo=nuxt&logoColor=white" alt="Nuxt 4">
</p>

---

## Ce que fait l'application

AleChemist accompagne une session de brassage du début à la fin, sans avoir à noter quoi que ce soit sur un carnet posé à côté de la cuve.

- **Import de recettes BeerXML.** Le fichier est analysé et converti en étapes typées, prêtes à être suivies.
- **Fiche recette** avec les tableaux Preparation, Mash, Boil, Yeasts et Ferment, chaque durée affichée dans son unité naturelle, minutes ou jours.
- **Brassins.** Plusieurs brassins par recette, chacun avec son propre déroulé et son historique.
- **Déroulé guidé en cinq étapes**, de la préparation à la fermentation, avec passage à la suivante quand tout est coché.
- **Chrono d'ébullition** qui suit les ajouts de houblon et d'aromatiques au fil du temps, annulable si le brassin a mal démarré.
- **Relevés** de densités avant ébullition, initiale et finale, plus volume, alcool et carbonatation.
- **Notes libres** par brassin, et récapitulatif complet une fois le brassin terminé.
- **Tableau de bord** qui met en avant le brassin en cours et archive les précédents.
- **Compte utilisateur** avec profil, mot de passe, double authentification TOTP et codes de récupération, liste des sessions actives et suppression de compte.
- **Bilingue** français et anglais, et **installable** comme application grâce au manifeste PWA.

## Stack

| Couche | Technologies |
|---|---|
| API | Laravel 13, PHP 8.5, MySQL 8 |
| Authentification | Fortify en mode headless (`views => false`), Sanctum en session par cookie |
| SPA | Nuxt 4 en génération statique (`ssr: false`), Vue 3, Tailwind CSS 4 |
| Modules front | `nuxt-auth-sanctum`, `@nuxtjs/i18n`, `@vite-pwa/nuxt` |
| Tests | PHPUnit 12, Vitest, Playwright pour les parcours manuels |
| Intégration continue | GitHub Actions, PHP 8.5 et Node 22 |
| Production | Laravel Forge sur Laravel VPS, nginx, déploiements sans interruption, mails par l'API Brevo |

## Architecture

Laravel n'expose plus aucune vue. Le backend est une API JSON, le front est une SPA statique, et les deux sont servis sur la **même origine** par nginx. L'authentification passe donc par un cookie de session, sans jeton à stocker côté client et sans CORS en production.

Le partage des URI se fait par préfixe, et par méthode pour les routes que Fortify et la SPA revendiquent toutes les deux.

| Requête | Servie par | Racine |
|---|---|---|
| `GET /` et toutes les routes de la SPA | fichiers statiques | `frontend/.output/public` |
| `/api/*`, `/sanctum/*`, `/user/*`, `/logout`, `/up` | PHP-FPM | `public/index.php` |
| `GET /login`, `/register`, `/forgot-password`, `/reset-password`, `/two-factor-challenge` | fichiers statiques, le shell de la SPA | `frontend/.output/public` |
| Les mêmes URI en POST | PHP-FPM, endpoints Fortify | `public/index.php` |

En développement, les deux serveurs sont séparés, la SPA sur le port 3000 et l'API sur le port 8000. C'est à cela que servent `FRONTEND_URL` et `SANCTUM_STATEFUL_DOMAINS` dans le `.env`.

## Mise en route

Il faut PHP 8.5, Composer, Node 20 ou plus, et une base MySQL.

```bash
# API
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve                 # http://localhost:8000
```

```bash
# SPA, dans un second terminal
cd frontend
npm install
npm run dev                       # http://localhost:3000
```

Le `.env.example` pointe l'envoi de mail vers Mailpit sur le port 1025. En production, le transport bascule sur l'API HTTP de Brevo, enregistré dans `AppServiceProvider`.

## Tests

```bash
vendor/bin/phpunit                # 85 tests cote API
cd frontend && npm run test:run   # Vitest sur les utilitaires de calcul
cd frontend && npm run typecheck  # vue-tsc
```

Les tests d'API couvrent le parcours Fortify complet, la session SPA Sanctum, les politiques d'accès, l'import BeerXML et le rendu réel des e-mails. Côté front, Vitest cible les modules purs, ébullition, EBC, fermentation, formats localisés, chemins, étapes et gestion d'erreurs.

## Déploiement

L'application tourne sur Laravel Forge, avec déploiements sans interruption. La particularité tient au vhost nginx, qui n'est pas celui d'une application Laravel classique puisque la racine web est le build de la SPA et non `public/`.

Le gabarit nginx, la liste des variables d'environnement de production et la checklist de vérification après bascule sont dans **[docs/deploiement-phase-4.md](docs/deploiement-phase-4.md)**.

Le script de déploiement construit la SPA sur le serveur. L'URL de l'API est figée au moment du build, via `NUXT_PUBLIC_SANCTUM_BASE_URL`, donc **tout changement de domaine impose un redéploiement**.

## Structure du dépôt

```
app/
  Actions/Fortify/      inscription, mot de passe, profil
  Http/Controllers/Api/ controleurs de l'API, un par ressource
  Http/Resources/       serialisation JSON stable
  Policies/             acces aux recettes et aux brassins
  Services/             BeerXmlImporter
database/migrations/    schema
docs/                   deploiement et design system
frontend/
  app/pages/            routes de la SPA
  app/components/       composants, dont le suivi d'ebullition
  app/utils/            calculs brassicoles et formats
  i18n/locales/         fr.json et en.json
  tests/                Vitest
lang/                   traductions cote API
routes/api.php          toutes les routes applicatives
tests/                  PHPUnit
```

## Documentation

- **[docs/design-system.md](docs/design-system.md)** décrit les conventions visuelles de la SPA, couleurs, typographie et composants.
- **[docs/deploiement-phase-4.md](docs/deploiement-phase-4.md)** décrit la mise en production, du vhost aux vérifications finales.
