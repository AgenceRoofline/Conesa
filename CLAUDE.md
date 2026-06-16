# Contexte projet — Conesa Rénovation

Site vitrine Astro v6 + Tailwind CSS v4 pour Conesa, entreprise familiale de rénovation dans le Tarn (81) depuis +55 ans.

## État d'avancement — Prochaines étapes (màj 2026-06-16)

### Pages services
| URL | Fichier | État |
|---|---|---|
| `/isolation` | `src/pages/isolation.astro` | ✅ |
| `/ravalement-facade` | `src/pages/ravalement-facade.astro` | ✅ |
| `/peinture-interieure-exterieure` | `src/pages/peinture-interieure-exterieure.astro` | ✅ |
| `/nettoyage-toiture` | `src/pages/nettoyage-toiture.astro` | ✅ |
| `/revetements-sols` | `src/pages/revetements-sols.astro` | ✅ |
| `/travaux-apres-sinistre` | `src/pages/travaux-apres-sinistre.astro` | ✅ |

Toutes les pages services sont créées. Contenu source : `contenu/Domaine métier/[slug].md`

### Liens Header/Footer
- Tous les liens services pointent vers les vraies URLs ✅ (plus de `/renovation#*`)

### Pages villes manquantes
Albi, Gaillac, Castres, Graulhet ✅ — À créer : **Carmaux, Lavaur, Réalmont, Mazamet, Saint-Sulpice**

### Tâches restantes avant mise en ligne
- [ ] Créer les 5 pages villes manquantes (Carmaux, Lavaur, Réalmont, Mazamet, Saint-Sulpice)
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
    Footer.astro                — formulaire de contact + carte Google Maps + liens
    RealisationsCarousel.astro  — carousel réalisations (lazy loading)
    ZonesIntervention.astro     — carte zones d'intervention
  layouts/
    BaseLayout.astro            — layout global (OG, canonical, noindex staging)
  pages/
    index.astro                 — page d'accueil (12 sections, V2)
    isolation.astro             — ✅ page service ITE
    ravalement-facade.astro     — ✅ page service ravalement
    peinture-interieure-exterieure.astro — ✅ page service peinture
    nettoyage-toiture.astro     — ✅ page service toiture
    revetements-sols.astro      — ✅ page service sols
    travaux-apres-sinistre.astro — ✅ page service sinistre
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
    Page revetements sols/      — (Hero revêtement de sols.png, Revêtement de sol.png)
    Page travaux apres sinistre/ — (Hero travaux après sinistre.png, Travaux après sinistre.png)
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

### Technique ✅ (ajouts récents)
- [x] JSON-LD `Service` + `BreadcrumbList` + `FAQPage` sur toutes les pages services
- [x] Carte Google Maps (iframe embed) dans `Footer.astro`

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

- [x] Créer les 6 pages services (`/nettoyage-toiture`, `/revetements-sols`, `/travaux-apres-sinistre`)
- [x] Corriger tous les liens Header + Footer vers les vraies URLs
- [ ] Créer les 5 pages villes manquantes (Carmaux, Lavaur, Réalmont, Mazamet, Saint-Sulpice)
- [ ] Passer SMTP de Gmail → Brevo (créer compte, récupérer clé SMTP)
- [ ] Changer `MAIL_TO` → `conesa81@wanadoo.fr`
- [ ] Renseigner hébergeur dans `mentions-legales.astro` (section 3, actuellement "À renseigner")
- [ ] Photos manquantes (réalisations, équipe, chantiers) — à fournir par le client
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
Tous les liens sont fonctionnels ✅

## Pages villes existantes

Albi, Gaillac, Castres, Graulhet — structure identique pour chaque ville :
- Hero avec `fetchpriority="high"` + `padding-top: 200px`
- Section présentation + chiffres clés + photo projet (`loading="lazy"`)
- FAQ locale en JSON-LD
- Liens internes vers pages services

## Conventions de code

- **Padding sections** : `py-8 lg:py-24` (mobile 32px, desktop 96px) — uniforme sur toutes les sections de toutes les pages et composants
- **Hero padding-top** : `style="padding-top: 200px; padding-bottom: 96px;"` — toutes les pages (services, villes, isolation). Valeur 200px pour laisser de l'espace sous le pré-header + header fixe.
- **Images hero** : `fetchpriority="high"` (pas de lazy loading — c'est le LCP)
- **Toutes les autres images** : `loading="lazy" decoding="async"`
- **Chemins images avec espaces/accents** : toujours encapsuler dans `encodeURI()` dans les attributs `src`
- **JSON-LD** : injecté via `<script is:inline slot="head" type="application/ld+json" set:html={JSON.stringify({...})} />`
- **noindex pages légales** : `<meta slot="head" name="robots" content="noindex, follow" />`
- **Scripts Astro (TypeScript)** : utiliser `Array.from(document.querySelectorAll<HTMLElement>('.class'))` + `.filter()` — évite les erreurs TS sur NodeList
- **Sections mobiles/desktop dupliquées** : quand un bloc existe en version mobile (`lg:hidden`) ET desktop (`hidden lg:block`), utiliser `<p>` (non sémantique) dans le bloc mobile pour éviter la duplication de `<h3>` dans le DOM — impacte l'arborescence des titres (audit accessibilité / SEO)

## Design système — Pages services

Chaque page service suit la même structure de sections, avec des rendus visuels **intentionnellement différents** pour éviter la répétition.

### Structure type d'une page service
1. **Hero** — image plein fond + breadcrumb + H1 + description + 2 CTAs (sans badge RGE — déjà dans le pré-header)
2. **Présentation** — 2 colonnes : texte + liste checkmarks / image + stats flottantes
3. **Diagnostic** — types de signes ou situations (section `bg-[#F0F5FC]`)
4. **Nos savoir-faire** — prestations détaillées (section `bg-white`)
5. **CTA intermédiaire** — bandeau `bg-[#1E4B8C]`
6. **Avantages** — bénéfices client (section `bg-[#F0F5FC]`)
7. **Étapes** — timeline numérotée (section `bg-white`)
8. **Pourquoi Conesa** — section `bg-[#F0F5FC]` fusionnée : 2-col (texte + "Nos engagements" fond bleu) + `border-t` + carte(s) maillage interne en `bg-white`
9. **ZonesIntervention** — composant carte (gère tout le contenu géographique — pas de section villes séparée)
10. **FAQ** — accordéon + JSON-LD FAQPage (section `bg-white`)
11. **CTA final** — bloc bleu arrondi avec motif (section `bg-[#F0F5FC]`)

### Design des sections (sans icônes — règle absolue)

**Section Diagnostic** (signes / types de situations) :
```html
<!-- Cards 2 colonnes, badge "Alerte" orange en tête, pas de chiffres ni d'icônes -->
<div class="grid sm:grid-cols-2 gap-4">
  {signes.map(s => (
    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-md hover:border-[#E8650A]/20 transition-all duration-200">
      <span class="inline-flex items-center gap-1 bg-[#E8650A]/10 text-[#E8650A] text-xs font-semibold px-2.5 py-1 rounded-full mb-4">
        <!-- icône warning SVG --> Alerte
      </span>
      <h3 ...>{s.titre}</h3>
      <p ...>{s.desc}</p>
    </div>
  ))}
</div>
```

**Section Prestations — 4 items** (checkerboard bleu/blanc) :
```html
<!-- Grille 2×2, alternance bg-[#1E4B8C] / bg-white via i % 2 -->
<div class="grid sm:grid-cols-2 gap-px bg-gray-200 rounded-3xl overflow-hidden shadow-sm">
  {prestations.map((p, i) => (
    <div class={`p-8 lg:p-10 ${i % 2 === 0 ? 'bg-[#1E4B8C]' : 'bg-white'}`}>
      <h3 class={i % 2 === 0 ? 'text-white' : 'text-[#1E4B8C]'}>{p.titre}</h3>
      <p class={i % 2 === 0 ? 'text-blue-200' : 'text-gray-600'}>{p.desc}</p>
    </div>
  ))}
</div>
```

**Section Prestations — 5-6 items** (grille simple, pas d'icônes) :
```html
<!-- Utilisé sur ravalement (5 items) et peinture (6 items avec badge) -->
<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
  {prestations.map(p => (
    <div class="bg-[#F0F5FC] rounded-2xl p-7 border border-transparent hover:border-[#1E4B8C]/20 hover:shadow-lg transition-all duration-300">
      <!-- optionnel : badge coloré (peinture uniquement) -->
      <h3 ...>{p.titre}</h3>
      <p ...>{p.desc}</p>
    </div>
  ))}
</div>
```

**Section Avantages** (barre d'accent colorée, pas d'icônes) :
```html
<!-- Barre h-1 w-12 colorée en tête, titre large, texte -->
<div class="grid sm:grid-cols-2 gap-8 lg:gap-12">
  {avantages.map((a, i) => (
    <div>
      <div class={`h-1 w-12 rounded-full mb-5 ${i % 2 === 0 ? 'bg-[#E8650A]' : 'bg-[#1E4B8C]'}`}></div>
      <h3 class="font-extrabold text-[#1E4B8C] text-xl mb-3">{a.titre}</h3>
      <p class="text-gray-600 text-sm leading-relaxed">{a.desc}</p>
    </div>
  ))}
</div>
<!-- Si avantages a un champ color (orange/blue), utiliser a.color à la place de i % 2 -->
```

**Pas de carousel sur les pages services** — les carousels ont été supprimés de toutes les pages services. Utiliser des grilles statiques uniquement.

**`entreprise.astro` — "Nos domaines d'expertise"** : liste éditoriale sur desktop (`hidden lg:block`), carousel snap horizontal sur mobile (`lg:hidden`). Les titres de services sont en `<h3>` dans le bloc desktop et en `<p>` dans le bloc mobile (évite duplication DOM).

### JSON-LD sur les pages services
Chaque page service injecte 3 blocs JSON-LD dans le `<head>` :
```astro
<script is:inline slot="head" type="application/ld+json" set:html={jsonLdService} />
<script is:inline slot="head" type="application/ld+json" set:html={jsonLdBreadcrumb} />
<script is:inline slot="head" type="application/ld+json" set:html={jsonLdFaq} />
```

### Footer — Google Maps
Section carte ajoutée entre le formulaire et les colonnes footer :
- Adresse : 31 Petit Chemin des Broucouniès, 81000 Albi
- Iframe Google Maps sans clé API : `?q=[adresse encodée]&hl=fr&z=16&output=embed`
- Lien "Ouvrir dans Google Maps" → `maps.google.com/?q=...` (target="_blank")

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
