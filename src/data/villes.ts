export interface Ville {
  slug: string;
  nom: string;
  departement: string;
  intro: string;
  heroTexte: string;
  zoneDescription: string;
}

export const villes: Ville[] = [
  {
    slug: "albi",
    nom: "Albi",
    departement: "Tarn (81)",
    intro: "Isolation thermique extérieure et rénovation à Albi",
    heroTexte: "Vous êtes propriétaire à Albi et souhaitez améliorer le confort de votre maison tout en réduisant vos factures d'énergie ? Conesa intervient dans toute l'agglomération albigeoise.",
    zoneDescription: "Albi et ses communes environnantes (Lescure-d'Albigeois, Le Séquestre, Marssac-sur-Tarn...)",
  },
  {
    slug: "gaillac",
    nom: "Gaillac",
    departement: "Tarn (81)",
    intro: "Isolation thermique extérieure et rénovation à Gaillac",
    heroTexte: "Propriétaire à Gaillac ? Conesa accompagne les habitants du Gaillacois dans leurs projets d'isolation extérieure et de rénovation de façade.",
    zoneDescription: "Gaillac et ses environs (Brens, Florentin, Técou...)",
  },
  {
    slug: "castres",
    nom: "Castres",
    departement: "Tarn (81)",
    intro: "Isolation thermique extérieure et rénovation à Castres",
    heroTexte: "Vous habitez à Castres et souhaitez rénover votre habitat ? Conesa met son expertise au service des propriétaires castrais pour des travaux durables et performants.",
    zoneDescription: "Castres et l'agglomération (Burlats, Labruguière, Dourgne...)",
  },
  {
    slug: "graulhet",
    nom: "Graulhet",
    departement: "Tarn (81)",
    intro: "Isolation thermique extérieure et rénovation à Graulhet",
    heroTexte: "Conesa intervient à Graulhet et dans ses environs pour tous vos projets d'isolation thermique extérieure et de rénovation de l'habitat.",
    zoneDescription: "Graulhet et le secteur (Lautrec, Giroussens, Saint-Gauzens...)",
  },
  {
    slug: "carmaux",
    nom: "Carmaux",
    departement: "Tarn (81)",
    intro: "Isolation thermique extérieure et rénovation à Carmaux",
    heroTexte: "Propriétaire à Carmaux ? Conesa vous accompagne dans l'amélioration de votre maison grâce à l'isolation thermique extérieure et la rénovation de façade.",
    zoneDescription: "Carmaux et ses communes (Blaye-les-Mines, Mirandol-Bourgnounac...)",
  },
  {
    slug: "lavaur",
    nom: "Lavaur",
    departement: "Tarn (81)",
    intro: "Isolation thermique extérieure et rénovation à Lavaur",
    heroTexte: "Vous êtes propriétaire à Lavaur ? Conesa réalise vos travaux d'isolation extérieure et de rénovation dans tout le secteur vauréen.",
    zoneDescription: "Lavaur et ses environs (Ambres, Massac-Séran, Saint-Paul-Cap-de-Joux...)",
  },
  {
    slug: "realmont",
    nom: "Réalmont",
    departement: "Tarn (81)",
    intro: "Isolation thermique extérieure et rénovation à Réalmont",
    heroTexte: "Conesa intervient à Réalmont et dans le secteur pour vos projets d'isolation thermique extérieure, de ravalement et d'entretien de l'habitat.",
    zoneDescription: "Réalmont et les communes alentours (Vénès, Ronel, Montredon-Labessonnié...)",
  },
  {
    slug: "mazamet",
    nom: "Mazamet",
    departement: "Tarn (81)",
    intro: "Isolation thermique extérieure et rénovation à Mazamet",
    heroTexte: "Propriétaire à Mazamet ? Conesa met son savoir-faire en isolation extérieure et rénovation au service des habitants du Mazamétain.",
    zoneDescription: "Mazamet et ses environs (Aussillon, La Montagne Noire...)",
  },
  {
    slug: "saint-sulpice",
    nom: "Saint-Sulpice",
    departement: "Tarn (81)",
    intro: "Isolation thermique extérieure et rénovation à Saint-Sulpice",
    heroTexte: "Vous habitez à Saint-Sulpice-la-Pointe ou dans ses environs ? Conesa intervient pour vos travaux d'isolation extérieure et de rénovation de l'habitat.",
    zoneDescription: "Saint-Sulpice-la-Pointe et le secteur (Couffouleux, Rabastens...)",
  },
];

export function getVilleBySlug(slug: string): Ville | undefined {
  return villes.find(v => v.slug === slug);
}
