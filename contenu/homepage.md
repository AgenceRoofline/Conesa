# Page d'accueil — Structure & contenu

Fichier source : `src/pages/index.astro`

---

## Ordre des sections

| # | Section | Fond | Composant / Notes |
|---|---------|------|-------------------|
| 1 | Hero | `#0A1E3D` (dark navy) | Plein écran, image de fond |
| 2 | Barre de stats | `#E8650A` (orange) | 4 chiffres clés |
| 3 | Les étapes | `#FFFFFF` | 5 étapes du projet |
| 4 | Particuliers & Professionnels | `#FFFFFF` | 2 cards côte à côte |
| 5 | ITE — Service phare | `#F0F5FC` | Focus isolation + logo RGE |
| 6 | Nos prestations | `#0A1E3D` (dark navy) | Stacking cards CSS sticky |
| 7 | Réalisations | `#F8F9FA` | `<RealisationsCarousel />` |
| 8 | Blog — Conseils & Guides | `#F0F5FC` | 4 articles en grille |
| 9 | Avis clients | `#FFFFFF` | Carousel 6 avis + lien Google |
| 10 | Zones d'intervention | — | `<ZonesIntervention />` |
| 11 | FAQ | `#FFFFFF` | 6 questions accordéon |
| 12 | CTA final | `#F0F5FC` | Card `#1E4B8C`, 2 boutons |

---

## Détail de chaque section

### 1. Hero
- Image de fond : `/images/Page accueil/hero-accueil.png`
- Overlay gradient : `rgba(10,30,61,0.88)` → `rgba(30,75,140,0.72)` → `rgba(10,30,61,0.60)`
- Grille de points blancs (opacity 5 %)
- Badge : "Entreprise familiale dans le Tarn depuis 1969"
- H1 : "Isolation Thermique Extérieure, Façade & Rénovation dans le Tarn"
- Sous-titre : 55 ans, particuliers, Tarn
- 2 boutons : "Demander un devis gratuit" (orange → `#contact`) + "Être rappelé" (`tel:0563541697`)

### 2. Barre de stats
- 4 valeurs : 55+ ans · 500+ chantiers · RGE Qualibat · 4,6/5

### 3. Les étapes
- Badge : "Les étapes"
- H2 : "Comment se déroule votre projet avec Conesa ?"
- 5 cards numérotées (01→05) : Prise de contact · Visite & devis gratuit · Montage des aides · Réalisation des travaux · Réception & suivi
- Mobile : scroll horizontal snap / Desktop : grille 3 colonnes (la 5e est centrée en lg)

### 4. Particuliers & Professionnels
- Badge : "Nos clients"
- H2 : "Nous intervenons pour les particuliers et les professionnels"
- **Colonne Particuliers** (fond blanc, bordure grise)
  - 5 items : ITE · Ravalement & peinture · Toiture · Rénovation intérieure · Aides MaPrimeRénov'
  - CTA bleu → `#contact`
- **Colonne Professionnels** (fond `#0A1E3D`, décors ronds)
  - 5 items : ITE copropriétés · Toiture bailleurs · Sinistres · Peinture en lots · Interlocuteur unique
  - CTA orange → `#contact`

### 5. ITE — Service phare
- Badge orange : "Notre expertise principale"
- H2 : "Isolation Thermique Extérieure : améliorez votre confort et réduisez vos factures"
- Image gauche : `/images/Page accueil/Isolation Thermique Extérieure.png` (ratio 4/3)
- Badge flottant : logo RGE Qualibat
- 5 bullets : économies · façade rénovée · aides financières · RGE · sans nuisance intérieure
- CTA bleu → `/isolation`

### 6. Nos prestations (stacking cards)
- Badge : "Nos prestations"
- H2 : "Tous vos travaux de rénovation avec un seul interlocuteur"
- Lien "Tout voir" → `/renovation`
- 5 cards en `position: sticky` (empilage au scroll, pure CSS, `--svc-i` CSS var)
- Chaque card : image gauche (`sm:w-80 lg:w-96`) + contenu droite (icône inline titre, description, 2 bullets, CTA orange)

| Prestation | Image | Lien |
|---|---|---|
| Ravalement de façade | `/images/Page rénovation/Ravalement de façade.png` | `/renovation#facade` |
| Peinture intérieure & extérieure | `/images/Page rénovation/Peinture intérieur et extérieure.png` | `/renovation#peinture` |
| Nettoyage & entretien de toiture | `/images/Page rénovation/Entretien toiture.png` | `/renovation#toiture` |
| Revêtements de sols | `/images/Page rénovation/Revêtement de sol.png` | `/renovation#sols` |
| Travaux après sinistre | `/images/Page rénovation/Travaux après sinistre.png` | `/renovation#sinistre` |

### 7. Réalisations
- Badge : "Portfolio"
- H2 : "Des réalisations qui témoignent de notre savoir-faire"
- Composant `<RealisationsCarousel />`

### 8. Blog — Conseils & Guides
- Badge : "Conseils & Guides"
- H2 : "Nos conseils pour votre habitat"
- Lien "Tous les articles" → `/blog`
- Grille 4 colonnes (sm:2, lg:4)
- 4 articles : Isolation intérieure/extérieure · Démoussage toiture · Rénovation énergétique · Façade ravalement

### 9. Avis clients
- Badge : "Témoignages"
- H2 : "La satisfaction de nos clients au cœur de nos engagements"
- Note 4,6/5 étoiles
- Carousel 6 avis avec flèches + dots + swipe tactile + autoplay 5 s
- Accordéon "Lire plus / Lire moins" (un seul ouvert à la fois)
- CTA "Laisser mon avis Google" (lien Google Maps)

### 10. Zones d'intervention
- Composant `<ZonesIntervention />`

### 11. FAQ
- Badge : "Questions fréquentes"
- H2 : "Tout ce que vous devez savoir"
- 6 questions accordéon (une seule ouverte à la fois) :
  1. Prix ITE
  2. Aides financières disponibles
  3. Zone d'intervention (Tarn)
  4. Prix ravalement de façade
  5. Délais d'intervention
  6. Comment obtenir un devis

### 12. CTA final
- Fond section : `#F0F5FC`
- Card intérieure : `#1E4B8C` avec motif hachures + cercle orange déco
- Badge : "Devis gratuit & sans engagement"
- H2 : "Vous avez un projet de rénovation dans le Tarn ?"
- 2 boutons : "Demander un devis gratuit" (orange) + "Nous contacter" (bordure blanche)

---

## Données structurées (JSON-LD)

- **LocalBusiness** : nom, adresse, téléphone, email, fondingDate 1969, priceRange ££, AggregateRating 4.6/5, areaServed (Albi, Gaillac, Castres, Graulhet, Tarn), sameAs Facebook
- **FAQPage** : 6 questions (mêmes que la section FAQ)

---

## Meta SEO

| Champ | Valeur |
|---|---|
| `<title>` | Isolation & Rénovation dans le Tarn \| Conesa |
| `description` | Isolation thermique extérieure, ravalement de façade et rénovation dans le Tarn. Entreprise familiale certifiée RGE depuis 1969. Devis gratuit. |
| Image OG | `/images/Logo/Open graph.png` (via BaseLayout) |
| Canonical | Auto-généré via `Astro.site` + `Astro.url.pathname` |
