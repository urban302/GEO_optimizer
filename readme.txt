=== GEORank ===
Contributors: erwinverbeek
Tags: geo, schema, structured data, ai seo, llms.txt
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Make your WordPress website visible to AI search engines like ChatGPT, Perplexity and Google AI Overviews.

== Description ==

GEORank helps you optimize your WordPress website for Generative Engine Optimization (GEO) — the new standard for visibility in AI search engines like ChatGPT Search, Perplexity, Google AI Overviews and other AI assistants.

Traditionele SEO zorgt dat je gevonden wordt in Google. GEO zorgt
dat AI-systemen jouw content begrijpen, citeren en aanbevelen.

= What does the plugin do? =

**Schema markup generator**
Automatically generates JSON-LD schema markup for all your pages and
posts. Supports Article, BlogPosting, FAQPage, Organization and
WebPage schema. Works alongside RankMath and Yoast SEO.

**llms.txt generator**
Automatically generates a /llms.txt file — the new standard
that allows AI crawlers like ChatGPT and Perplexity to understand your site.
Contains all pages, posts and meta descriptions in AI-readable
Markdown format.

**GEO Score per pagina**
Toont een score van 0-100 per pagina in de WordPress editor,
gebaseerd op aanwezigheid van schema markup, FAQ blokken,
auteursinformatie, interne links en content lengte. Inclusief
groene/oranje/rode indicatoren per factor.

**Brand entity configuratie**
Stel je organisatie, social media profielen en adresgegevens in.
De plugin gebruikt deze data voor consistente entity markup
door je hele site.

= Why GEO? =

More than 30% of informational search queries now end at an
AI answer instead of a traditional search results page.
Websites that are not GEO-optimized miss this visibility entirely.
GEORank is the first WordPress plugin focused exclusively
on AI search engine optimization.

= Works with =

* RankMath SEO
* Yoast SEO
* All in One SEO
* Gutenberg editor
* Classic editor

= About the developer =

GEORank was developed by Erwin Verbeek, SEO specialist with
20+ years of experience and expert in GEO and AI-driven search engine
optimization. More information at
[erwinverbeek.nl/geo-optimizer](https://www.erwinverbeek.nl/geo-optimizer/).

== Installation ==

1. Upload de `geo-optimizer` map naar `/wp-content/plugins/`
2. Activeer de plugin via het 'Plugins' menu in WordPress
3. Ga naar Instellingen > GEORank
4. Vul je organisatiegegevens in op het tabblad "Organisatie"
5. Controleer je /llms.txt via jouwsite.nl/llms.txt

== Frequently Asked Questions ==

= Werkt deze plugin samen met RankMath of Yoast? =

Yes. GEORank detecteert automatisch of RankMath of Yoast
actief is. De schema markup output wordt dan uitgeschakeld om
conflicten te voorkomen. De llms.txt generator en GEO Score
werken altijd, ongeacht andere SEO plugins.

= Wat is llms.txt? =

llms.txt is een nieuw bestandsformaat (vergelijkbaar met robots.txt)
waarmee je AI-crawlers vertelt hoe ze jouw website moeten begrijpen.
ChatGPT, Perplexity en andere AI-systemen gebruiken dit bestand om
context over jouw site op te bouwen.

= Wat is GEO? =

GEO staat voor Generative Engine Optimization — het optimaliseren
van content zodat AI-systemen jouw website correct begrijpen,
citeren en aanbevelen in hun antwoorden.

= Heeft de plugin een Pro versie? =

Ja. GEORank Pro bevat AI-powered content analyse,
automatische FAQ suggesties, citeer-score per pagina
en competitor gap analyse. Meer informatie op
erwinverbeek.nl/geo-optimizer/

= Werkt de plugin met de Gutenberg editor? =

Ja. De GEO Score meta box is zichtbaar in zowel de Gutenberg
editor als de Classic editor. FAQ schema wordt automatisch
gegenereerd op basis van core/details blokken in Gutenberg.

== Screenshots ==

1. GEO Score meta box in de WordPress editor
2. JSON-LD schema output in de broncode
3. llms.txt gegenereerd door de plugin
4. Settings pagina met organisatie configuratie

== Changelog ==

= 1.0.0 =
* Initial release
* Schema markup generator (Article, FAQPage, Organization, WebPage)
* llms.txt generator
* GEO Score meta box (0-100 per pagina)
* Brand entity configuratie
* Detectie en compatibiliteit met RankMath en Yoast SEO

== Upgrade Notice ==

= 1.0.0 =
Initial release of GEORank.
