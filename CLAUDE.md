# Contexte projet — Conesa Rénovation

Site vitrine Astro v6 + Tailwind CSS v4 pour Conesa, entreprise familiale de rénovation dans le Tarn (81) depuis +55 ans.

## État d'avancement — Prochaines étapes (màj 2026-06-12)

### Pages services
| URL | Fichier | État |
|---|---|---|
| `/isolation` | `src/pages/isolation.astro` | ✅ |
| `/ravalement-facade` | `src/pages/ravalement-facade.astro` | ✅ |
| `/peinture-interieure-exterieure` | `src/pages/peinture-interieure-exterieure.astro` | ✅ |
| `/nettoyage-toiture` | à créer | ❌ 404 |
| `/revetements-sols` | à créer | ❌ 404 |
| `/travaux-apres-sinistre` | à créer | ❌ 404 |

Contenu source des pages manquantes : `contenu/Domaine métier/[slug].md`

### Liens Header/Footer à corriger (après création des pages)
- `Header.astro` : Toiture → `/renovation#toiture`, Sols → `/renovation#sols`, Après sinistre → `/renovation#sinistre` — **à remplacer par les vraies URLs**
- `Footer.astro` : idem, le tableau `services` pointe encore vers `/renovation#*`

### Pages villes manquantes
Albi, Gaillac, Castres, Graulhet ✅ — À créer : **Carmaux, Lavaur, Réalmont, Mazamet, Saint-Sulpice**

### Tâches restantes avant mise en ligne
- [ ] Créer `/nettoyage-toiture`, `/revetements-sols`, `/travaux-apres-sinistre`
- [ ] Créer les 5 pages villes manquantes
- [ ] Corriger les liens Header + Footer vers les nouvelles pages services
- [ ] Passer SMTP Gmail → Brevo + `MAIL_TO` → `conesa81@wanadoo.fr`
- [ ] Renseigner hébergeur dans `mentions-legales.astro` (section 3 : "À renseigner")
- [ ] Photos manquantes (réalisations, équipe) à fournir par le client
- [ ] Conversion images en WebP
- [ ] Mettre à jour `SITE_URL` avec le domaine final

---

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
    index.astro                 — page d'accueil (12 sections, V2)
    isolation.astro             — ✅ page service ITE
    ravalement-facade.astro     — ✅ page service ravalement
    peinture-interieure-exterieure.astro — ✅ page service peinture
    renovation.astro            — ancienne page agrégat (conservée)
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
      Logo-Conesa.svg           — logo principal (SVG)
      Logo-Conesa.png           — fallback PNG
      Open graph.png            — image OG (1200×630, dans public/images/Logo/)
    labels/Logo-RGE.png
    Page accueil/               — images homepage
    Page isolation ext/         — images ITE
    Page ravalement de façade/  — images ravalement (Rénovation façade.png)
    Page peinture interieure exterieure/ — (Peinture intérieur et extérieure.png)
    Page nettoyage toiture/     — (Hero nettoyage toiture.png, Entretien toiture.png)
    Page revetements sols/      — (Revêtement de sol.png)
    Page travaux apres sinistre/ — (Travaux après sinistre.png)
    Entreprise/
    Villes/
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

## Sécurité — État actuel

### Protections en place

| Protection | Implémentation | Fichier |
|---|---|---|
| CSRF Double Submit Cookie | Token 48 chars hex (CSPRNG), `SameSite=Strict`, `Secure` en HTTPS | `Footer.astro` |
| Honeypot anti-spam | Champ caché, réponse silencieuse `success:true` | `Footer.astro` / `contact.php` |
| Rate limiting | 5 soumissions/h par session, fenêtre glissante | `contact.php` |
| Validation serveur | Regex, `filter_var`, whitelist prestation | `contact.php` |
| Sanitisation | `strip_tags` + `trim` + `mb_substr` + `htmlspecialchars` | `contact.php` |
| Injections SQL | Requêtes PDO préparées uniquement | `contact.php` |
| Hash IP | SHA-256 de `REMOTE_ADDR` (jamais IP brute) | `contact.php` |
| Purge RGPD | DELETE leads > 24 mois à chaque soumission | `contact.php` |
| Protection `.env` | `Require all denied` dans htaccess | `deploy/*/www.htaccess` |
| Protection `storage/` | `Require all denied` + `RewriteRule [F]` double | `deploy/*/www.htaccess` + `storage.htaccess` |
| HSTS | `max-age=31536000; includeSubDomains` | `deploy/prod/www.htaccess` uniquement |
| CSP | `script-src 'self' 'unsafe-inline'` + Google Fonts | `deploy/*/www.htaccess` |
| X-Frame-Options | `DENY` + `frame-ancestors 'none'` (CSP) | `deploy/*/www.htaccess` |
| X-Content-Type-Options | `nosniff` | `deploy/*/www.htaccess` |
| Referrer-Policy | `strict-origin-when-cross-origin` | `deploy/*/www.htaccess` |
| Permissions-Policy | camera, micro, géolocalisation désactivés | `deploy/*/www.htaccess` |
| form-action | `'self'` — formulaire ne peut soumettre qu'en interne | CSP dans htaccess |
| noindex staging | `PUBLIC_STAGING=true` → meta noindex global | `BaseLayout.astro` |
| Basic Auth staging | Accès restreint par mot de passe sur préprod | `deploy/staging/www.htaccess` |

### Notes importantes

- **HSTS** : présent dans `deploy/prod/www.htaccess` uniquement. **Ne pas activer en staging tant que SSL n'est pas validé.** Une fois activé avec `includeSubDomains`, tous les sous-domaines OVH doivent être en HTTPS — irréversible pendant 1 an.
- **CSP `'unsafe-inline'`** : nécessaire car Astro génère des scripts inline en build statique (pas de SSR = pas de nonces). Le bénéfice reste réel : `form-action 'self'`, `connect-src 'self'`, `frame-ancestors 'none'` protègent contre les vecteurs d'attaque les plus courants.
- **Rate limiting session** : contournable en incognito. Si spam constaté en prod, envisager reCAPTCHA v3 invisible.
- **Purge RGPD** : s'exécute à chaque soumission de formulaire, pas de cron nécessaire. Supprime les leads dont `created_at < datetime('now', 'localtime', '-24 months')`.

### Niveau de sécurité (post-audit)

| Domaine | Note | Évolution |
|---|---|---|
| Sécurité formulaire | **8.5/10** | +0.5 (cookie CSRF Secure) |
| Sécurité des données | **8/10** | +0.5 (purge RGPD 24 mois) |
| Sécurité serveur | **9/10** | +2 (HSTS + CSP + form-action + frame-ancestors) |
| **Sécurité globale** | **8.5/10** | +1 depuis l'audit initial |

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
- [ ] Valider SSL/HTTPS actif → activer HSTS dans `deploy/prod/www.htaccess` (décommenter si nécessaire)
- [ ] Google Analytics ou Matomo (optionnel)
- [ ] reCAPTCHA v3 (optionnel, si spam)

## Page d'accueil V2 — Structure (12 sections)

`src/pages/index.astro` — dernière version commitée sur `main`

1. **Hero** — H1 + description + 2 CTA (Devis gratuit / Être rappelé)
2. **Stats bar** — 4 chiffres clés sur fond orange (`#E8650A`)
3. **Présentation entreprise** — 2 colonnes : texte + 6 badges réassurance + CTA `/entreprise`
4. **Particuliers & Pros** — 2 cards interactives avec effet **zoom/blur** :
   - Active : `transform: scale(1.05)` + ombre portée
   - Inactive : `filter: blur(3px); opacity: 0.5`
   - Classes CSS : `.pp-zoom`, `.pp-active`, `.pp-inactive`
   - Script : `Array.from(document.querySelectorAll<HTMLElement>('.pp-zoom'))` (pattern TypeScript-safe pour Astro)
5. **Nos prestations** (stacking cards) — fond `#0A1E3D`, 6 cards sticky (`position: sticky`, `--svc-i` CSS custom property), sous-titre "Une expertise complète pour votre habitat" avec description 2 colonnes
6. **Les étapes** — 5 étapes numérotées
7. **Réalisations** — composant `RealisationsCarousel`
8. **Blog** — 4 articles, grille 4 colonnes
9. **Avis clients** — 6 témoignages + lien Google
10. **Zones d'intervention** — composant `ZonesIntervention`
11. **FAQ** — accordéon 6 questions + JSON-LD FAQPage
12. **CTA Final** — bloc bleu double bouton

### Stacking cards — liens services dans index.astro
```js
const services = [
  { titre: "Isolation Thermique Extérieure", href: "/isolation", image: "/images/Page isolation ext/..." },
  { titre: "Ravalement de façade",           href: "/ravalement-facade", image: "/images/Page ravalement de façade/Rénovation façade.png" },
  { titre: "Peinture intérieure & extérieure", href: "/peinture-interieure-exterieure", image: "/images/Page peinture interieure exterieure/Peinture intérieur et extérieure.png" },
  { titre: "Nettoyage & entretien de toiture", href: "/nettoyage-toiture", image: "/images/Page nettoyage toiture/Entretien toiture.png" },
  { titre: "Revêtements de sols",            href: "/revetements-sols", image: "/images/Page revetements sols/Revêtement de sol.png" },
  { titre: "Travaux après sinistre",         href: "/travaux-apres-sinistre", image: "/images/Page travaux apres sinistre/Travaux après sinistre.png" },
]
```
⚠️ Les 3 derniers liens (nettoyage, sols, sinistre) mènent vers des **404** — pages à créer.

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
- **Chemins images avec espaces/accents** : toujours encapsuler dans `encodeURI()` dans les attributs `src`
- **JSON-LD** : injecté via `<script is:inline slot="head" type="application/ld+json" set:html={JSON.stringify({...})} />`
- **noindex pages légales** : `<meta slot="head" name="robots" content="noindex, follow" />`
- **Scripts Astro (TypeScript)** : utiliser `Array.from(document.querySelectorAll<HTMLElement>('.class'))` + `.filter()` — évite les erreurs TS sur NodeList

## Git — Fichiers sensibles à ne JAMAIS committer

Ces fichiers existent à la racine du projet mais sont hors git (`.gitignore`) :
- `.htpasswd` — identifiants Basic Auth staging
- `debug.php`, `info.php` — outils de debug temporaires
- `Contact.php`, `formulaire.js`, `header.php` — fichiers de test/draft isolés
- `.env`, `.env.staging` — variables d'environnement

Lors d'un `git add`, ne stager **que** les fichiers `src/`, `public/`, `contenu/`, `deploy/`, et les fichiers de config Astro/Tailwind.

## GitHub

```
https://github.com/AgenceRoofline/Conesa.git
```
