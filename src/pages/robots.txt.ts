import type { APIRoute } from 'astro';

export const GET: APIRoute = ({ site }) => {
  const isStaging = import.meta.env.PUBLIC_STAGING === 'true';
  const siteHref  = site?.href ?? 'https://conesa-renovation.fr/';

  const content = isStaging
    ? 'User-agent: *\nDisallow: /\n'
    : `User-agent: *\nAllow: /\nDisallow: /contact.php\nSitemap: ${siteHref}sitemap-index.xml\n`;

  return new Response(content, {
    headers: { 'Content-Type': 'text/plain; charset=utf-8' },
  });
};
