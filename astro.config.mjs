// @ts-check
import { defineConfig } from 'astro/config';
import { loadEnv } from 'vite';
import tailwindcss from '@tailwindcss/vite';
import sitemap from '@astrojs/sitemap';

// Détecte le mode passé via --mode (ex: astro build --mode staging)
const modeIdx = process.argv.indexOf('--mode');
const mode = modeIdx !== -1 ? process.argv[modeIdx + 1] : 'production';
const env = loadEnv(mode, process.cwd(), '');

const SITE_URL = env.SITE_URL || 'https://conesa-renovation.fr';

export default defineConfig({
  site: SITE_URL,
  integrations: [
    sitemap({
      filter: (page) =>
        !page.includes('/mentions-legales') &&
        !page.includes('/politique-confidentialite'),
    }),
  ],
  vite: {
    plugins: [tailwindcss()]
  }
});