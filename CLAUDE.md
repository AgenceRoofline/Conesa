# Contexte projet — Conesa Rénovation

Site vitrine Astro v6 + Tailwind CSS v4 pour Conesa, entreprise familiale de rénovation dans le Tarn (81) depuis +55 ans.

## Stack technique

- **Framework** : Astro v6.4.2 (site statique, pas de SSR)
- **CSS** : Tailwind CSS v4
- **PHP** : contact.php dans `public/` → copié dans `dist/` au build
- **Dépendances PHP** : PHPMailer ^6.9 + phpdotenv ^5.6 (via Composer)
- **Dev local** : WAMP + virtual host `conesa.local` → pointe sur `dist/`
- **Build** : `npm run build` obligatoire après chaque modif de `public/contact.php`

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
    Header.astro          — navigation fixe
    Footer.astro          — formulaire de contact + liens
    RealisationsCarousel.astro
    ZonesIntervention.astro
  layouts/
    BaseLayout.astro
  pages/
    index.astro           — page d'accueil (9 sections)
    isolation.astro
    renovation.astro
    entreprise.astro
    mentions-legales.astro
    politique-confidentialite.astro
    villes/
      albi.astro / gaillac.astro / castres.astro / graulhet.astro
public/
  contact.php             — backend formulaire (PHP + PHPMailer)
  images/
storage/
  leads.sqlite            — base SQLite leads (créée auto au 1er envoi, ignorée par git)
contenu/                  — fichiers .md source de contenu
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

## Infos client

- **Société** : CONESA SAS — SIREN 429 132 053
- **Adresse** : 31 Petit Chemin des Broucouniès, 81000 Albi
- **Tél** : 05 63 54 16 97
- **Email** : conesa81@wanadoo.fr
- **Dirigeant** : Julien CONESA
- **Note Google** : 4,6/5

## Avant mise en ligne (checklist)

- [ ] Passer SMTP de Gmail → Brevo (créer compte, récupérer clé SMTP)
- [ ] Changer MAIL_TO → `conesa81@wanadoo.fr`
- [ ] Renseigner hébergeur dans `mentions-legales.astro`
- [ ] Photos manquantes (réalisations, équipe, chantiers)
- [ ] Installer `@astrojs/sitemap`
- [ ] `composer install` sur le serveur de prod
- [ ] Vérifier `session_start()` chez l'hébergeur
- [ ] Google Analytics ou Matomo (optionnel)
- [ ] reCAPTCHA v3 (optionnel, si spam)

## Pages villes existantes

Albi, Gaillac, Castres, Graulhet — les autres (Carmaux, Lavaur, Réalmont, Mazamet, Saint-Sulpice) sont à créer.

## GitHub

```
https://github.com/AgenceRoofline/Conesa.git
```
