# Contexte projet — Conesa Rénovation

Site vitrine Astro v6 + Tailwind CSS v4 pour Conesa, entreprise familiale de rénovation dans le Tarn (81) depuis +55 ans.

## Stack technique

- **Framework** : Astro v6.4.2 (site statique, pas de SSR)
- **CSS** : Tailwind CSS v4
- **PHP** : contact.php dans `public/` → copié dans `dist/` au build
- **Dépendances PHP** : PHPMailer ^6.9 + phpdotenv ^5.6 (via Composer)
- **Sitemap** : `@astrojs/sitemap` intégré — généré automatiquement au build
- **Dev local** : WAMP + virtual host `conesa.local` → pointe sur `dist/`
- **Build prod** : `npm run build`
- **Build staging** : `npm run build:staging` (charge `.env.staging`)

## Charte graphique

- **Bleu principal** : `#1E4B8C` (titres, boutons, éléments de réassurance)
- **Bleu foncé** : `#153566` (footer, hover)
- **Orange** : `#E8650A` (CTA, chiffres clés, accents)
- **Fond clair** : `#F0F5FC`
- **Typo titres** : Montserrat (via `style="font-family:'Montserrat',sans-serif;"`)

## Structure fichiers

```
src/
  components/
    Header.astro                — navigation fixe (desktop dropdown + mobile split lien/flèche)
    Footer.astro                — formulaire de contact + liens
    RealisationsCarousel.astro  — carousel réalisations (lazy loading)
    ZonesIntervention.astro     — carte zones d'intervention
  layouts/
    BaseLayout.astro            — layout global (OG, canonical, noindex staging)
  pages/
    index.astro                 — page d'accueil (9 sections)
    isolation.astro
    renovation.astro
    entreprise.astro
    mentions-legales.astro      — noindex forcé
    politique-confidentialite.astro — noindex forcé
    robots.txt.ts               — robots.txt dynamique (bloque tout en staging)
    villes/
      albi.astro / gaillac.astro / castres.astro / graulhet.astro
      — À créer : Carmaux, Lavaur, Réalmont, Mazamet, Saint-Sulpice
public/
  contact.php                   — backend formulaire (PHP + PHPMailer)
  images/
    Logo/
      Logo-Conesa.png
      Open graph.png            — image OG (1200×630)
    labels/Logo-RGE.png
    Page accueil / Page-isolation-ext / Page rénovation / Entreprise / Villes/
storage/
  leads.sqlite                  — base SQLite leads (créée auto, ignorée par git)
deploy/
  staging/
    www.htaccess                — fusionné : Basic Auth + .env + storage/ + headers sécu (→ www/.htaccess)
  prod/
    www.htaccess                — production : .env + storage/ + headers sécu (→ www/.htaccess)
    storage.htaccess            — à déposer dans www/storage/ : Require all denied
contenu/                        — fichiers .md source de contenu
.env.staging.example            — modèle de config pour l'environnement de préprod
```

## Formulaire de contact

- **Sécurité** : CSRF (Double Submit Cookie), honeypot, rate limiting (5/h), validation serveur
- **SMTP dev** : Gmail `smtp.gmail.com:587` via App Password
- **SMTP prod** : Brevo `smtp-relay.brevo.com:587` (à configurer avant mise en ligne)
- **SQLite** : chaque soumission → INSERT dans `storage/leads.sqlite` (status pending → sent/failed)
- **Variables** : fichier `.env` à la racine (non commité)

### .env dev (à recréer manuellement sur chaque poste)
```
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=com.roofline@gmail.com
SMTP_PASS="mot de passe app Gmail"
MAIL_FROM=com.roofline@gmail.com
MAIL_TO=com.roofline@gmail.com
```

## SEO — État actuel

Tout ce qui est coché est déjà implémenté dans le code.

### Technique ✅
- [x] `robots.txt` dynamique (`src/pages/robots.txt.ts`) — bloque tout en staging, normal en prod
- [x] Sitemap XML auto-généré par `@astrojs/sitemap` (exclut mentions-légales et politique de conf.)
- [x] Canonical URL sur toutes les pages (via `Astro.site` + `Astro.url.pathname`)
- [x] Open Graph + Twitter Card dans `BaseLayout.astro`
- [x] Image OG : `/images/Logo/Open graph.png` (1200×630, dans `public/`)
- [x] JSON-LD `LocalBusiness` + `AggregateRating` (4,6/5) sur `index.astro`
- [x] JSON-LD `FAQPage` sur toutes les pages (index, isolation, renovation, entreprise, villes)
- [x] `noindex, follow` sur `mentions-legales.astro` et `politique-confidentialite.astro`
- [x] `noindex, nofollow` global en mode staging (via `PUBLIC_STAGING=true`)
- [x] Meta titles < 60 chars avec suffixe ` | Conesa`
- [x] Meta descriptions 120–155 chars sur toutes les pages
- [x] Alt descriptifs sur tous les héros
- [x] `fetchpriority="high"` sur les images hero (signal LCP)
- [x] `loading="lazy"` + `decoding="async"` sur toutes les images hors viewport

### À faire
- [ ] Maillage interne : pages villes → pages services (isolation, rénovation)
- [ ] Breadcrumbs JSON-LD (`BreadcrumbList`) sur les pages villes
- [ ] Conversion images en WebP
- [ ] Google Business Profile lié au site
- [ ] 5 pages villes manquantes (Carmaux, Lavaur, Réalmont, Mazamet, Saint-Sulpice)

## Staging (préprod)

- **URL** : `https://conesa.roofline.fr`
- **Dossier OVH** : `/conesa/`
- **Structure** : tout dans `/conesa/` (vendor/, .env, storage/ cohabitent avec les fichiers publics)

### Workflow de déploiement préprod

```bash
# 1. Créer .env.staging à partir du modèle
cp .env.staging.example .env.staging
# → Remplir SMTP_PASS (le SITE_URL est déjà correct : https://conesa.roofline.fr)

# 2. Builder + dépendances PHP
npm run build:staging
composer install --no-dev

# 3. Uploader via FTP sur OVH
#    dist/*          → /conesa/
#    vendor/         → /conesa/vendor/
#    .env.staging    → /conesa/.env   (renommer au dépôt)
#    storage/        → /conesa/storage/

# 4. Déposer les .htaccess
#    deploy/staging/www.htaccess   → /conesa/.htaccess
#    deploy/prod/storage.htaccess  → /conesa/storage/.htaccess

# 5. Créer le .htpasswd hors du dossier /conesa/ et mettre à jour AuthUserFile
#    dans /conesa/.htaccess avec le chemin absolu OVH (/home/proXXXXXX/.htpasswd)
#    htpasswd -c /home/proXXXXXX/.htpasswd conesa
```

> **Note contact.php** : avec tout dans `/conesa/`, `dirname(__DIR__)` remonte au parent de `/conesa/` (qui n'a pas de `vendor/`), donc le code bascule automatiquement sur `__DIR__` (auto-détection déjà en place).

### .htaccess à déployer

| Fichier local | Destination OVH | Rôle |
|---|---|---|
| `deploy/staging/www.htaccess` | `/conesa/.htaccess` | Basic Auth + .env + storage/ + headers sécu |
| `deploy/prod/www.htaccess` | `/conesa/.htaccess` (prod uniquement) | .env + storage/ + headers sécu (sans Basic Auth) |
| `deploy/prod/storage.htaccess` | `/conesa/storage/.htaccess` | Bloque tout accès HTTP à leads.sqlite |

### Comportement en mode staging (`PUBLIC_STAGING=true`)
- `<meta name="robots" content="noindex, nofollow">` injecté sur toutes les pages
- `robots.txt` généré avec `Disallow: /` (bloque tous les bots)
- `site:` dans astro.config.mjs utilise `SITE_URL=https://conesa.roofline.fr`

### Vérifications après déploiement préprod

```bash
# Robots bloqués
curl -s -u conesa:motdepasse https://conesa.roofline.fr/robots.txt
# → doit contenir "Disallow: /"

# .env inaccessible
curl -s -u conesa:motdepasse https://conesa.roofline.fr/.env
# → doit retourner 403

# SQLite inaccessible
curl -s -u conesa:motdepasse https://conesa.roofline.fr/storage/leads.sqlite
# → doit retourner 403
```

Checklist manuelle :
- [ ] Basic Auth demandée à l'ouverture du site
- [ ] Pages affichent `<meta name="robots" content="noindex, nofollow">`
- [ ] Formulaire → lead enregistré (`pending` → `sent`) + mail reçu
- [ ] Leads SQLite visibles via FTP dans `/conesa/storage/leads.sqlite`

### Pour revenir en prod
```bash
npm run build   # sans --mode staging → robots.txt normal, pas de noindex
# Uploader sur le domaine final, déployer deploy/prod/www.htaccess → .htaccess (sans Basic Auth)
```

## Déploiement OVH

### Workflow

```
Claude Code → dev local → git commit → npm run build → upload dist/ sur OVH
```

La source officielle reste Git. OVH n'est jamais la source du projet.

### Commandes avant chaque mise en ligne

```bash
npm install
npm run build          # (ou npm run build:staging pour la préprod)
composer install --no-dev
```

### Fichiers à uploader sur OVH

```
dist/*        → www/           (tous les fichiers générés par Astro)
vendor/       → selon structure ci-dessous
.env          → selon structure ci-dessous
storage/      → selon structure ci-dessous
```

### Structure idéale (si OVH permet des dossiers hors web root)

```
/home/user/
├── www/                ← web root pointé par OVH
│   ├── index.html
│   ├── contact.php
│   └── assets/
├── storage/            ← hors public (inaccessible HTTP)
│   └── leads.sqlite
├── vendor/             ← hors public
└── .env                ← hors public
```

`contact.php` utilise `dirname(__DIR__)` pour remonter au-dessus de `www/` et trouver `vendor/`, `.env`, `storage/`.

### Structure fallback (si OVH impose tout dans www/)

```
www/
├── index.html
├── contact.php
├── vendor/
├── .env
├── storage/
│   ├── leads.sqlite
│   └── .htaccess       ← deploy/prod/storage.htaccess (Require all denied)
└── .htaccess           ← deploy/prod/www.htaccess (protège .env et storage/)
```

`contact.php` détecte automatiquement cette structure et ajuste `$root` en conséquence (auto-détection dans `public/contact.php`).

### .htaccess à déployer

| Fichier local | Destination sur OVH | Rôle |
|---|---|---|
| `deploy/prod/www.htaccess` | `www/.htaccess` | Protège `.env`, `storage/`, headers sécu |
| `deploy/prod/storage.htaccess` | `www/storage/.htaccess` | Bloque tout accès HTTP à SQLite |

### Vérifications avant mise en ligne OVH (prod)

- [ ] SQLite fonctionne sur l'hébergement (PHP PDO SQLite activé)
- [ ] PHP a les droits d'écriture sur `storage/`
- [ ] `.env` inaccessible depuis le navigateur (`curl https://domaine.fr/.env` → 403)
- [ ] `leads.sqlite` inaccessible depuis le navigateur → 403
- [ ] Envoi SMTP fonctionne (tester le formulaire)
- [ ] Leads enregistrés + statut `sent` dans SQLite

## Infos client

- **Société** : CONESA SAS — SIREN 429 132 053
- **Adresse** : 31 Petit Chemin des Broucouniès, 81000 Albi
- **Tél** : 05 63 54 16 97
- **Email** : conesa81@wanadoo.fr
- **Dirigeant** : Julien CONESA
- **Note Google** : 4,6/5

## Checklist avant mise en ligne (prod)

- [ ] Passer SMTP de Gmail → Brevo (créer compte, récupérer clé SMTP)
- [ ] Changer `MAIL_TO` → `conesa81@wanadoo.fr`
- [ ] Renseigner hébergeur dans `mentions-legales.astro` (section 3, actuellement "À renseigner")
- [ ] Photos manquantes (réalisations, équipe, chantiers) — à fournir par le client
- [ ] Créer les 5 pages villes manquantes (Carmaux, Lavaur, Réalmont, Mazamet, Saint-Sulpice)
- [ ] Mettre à jour `SITE_URL` dans `.env` (ou `astro.config.mjs`) avec le domaine réel
- [ ] `composer install --no-dev` sur le serveur de prod
- [ ] Vérifier `session_start()` chez l'hébergeur
- [ ] Google Analytics ou Matomo (optionnel)
- [ ] reCAPTCHA v3 (optionnel, si spam)

## Pages villes existantes

Albi, Gaillac, Castres, Graulhet — structure identique pour chaque ville :
- Hero avec `fetchpriority="high"`
- Section présentation + chiffres clés + photo projet (`loading="lazy"`)
- FAQ locale en JSON-LD
- Liens internes vers pages services

## Conventions de code

- **Padding sections** : `py-8 lg:py-24` (mobile 32px, desktop 96px) — uniforme sur toutes les sections de toutes les pages et composants
- **Images hero** : `fetchpriority="high"` (pas de lazy loading — c'est le LCP)
- **Toutes les autres images** : `loading="lazy" decoding="async"`
- **JSON-LD** : injecté via `<script is:inline slot="head" type="application/ld+json" set:html={JSON.stringify({...})} />`
- **noindex pages légales** : `<meta slot="head" name="robots" content="noindex, follow" />`

## GitHub

```
https://github.com/AgenceRoofline/Conesa.git
```
