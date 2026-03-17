=== GEO Optimizer ===
Contributors: erwinverbeek
Tags: geo, schema, structured data, ai seo, llms.txt
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Maak je WordPress website vindbaar voor AI-zoekmachines zoals
ChatGPT, Perplexity en Google AI Overviews.

== Description ==

GEO Optimizer helpt je WordPress website optimaliseren voor
Generative Engine Optimization (GEO) — de nieuwe standaard voor
zichtbaarheid in AI-zoekmachines zoals ChatGPT Search, Perplexity,
Google AI Overviews en andere AI-assistenten.

Traditionele SEO zorgt dat je gevonden wordt in Google. GEO zorgt
dat AI-systemen jouw content begrijpen, citeren en aanbevelen.

= Wat doet de plugin? =

**Schema markup generator**
Genereert automatisch JSON-LD schema markup voor al je pagina's en
posts. Ondersteunt Article, BlogPosting, FAQPage, Organization en
WebPage schema. Werkt naast RankMath en Yoast SEO.

**llms.txt generator**
Genereert automatisch een /llms.txt bestand — de nieuwe standaard
waarmee AI-crawlers zoals ChatGPT en Perplexity jouw site begrijpen.
Bevat alle pagina's, posts en meta-omschrijvingen in AI-leesbaar
Markdown formaat.

**GEO Score per pagina**
Toont een score van 0-100 per pagina in de WordPress editor,
gebaseerd op aanwezigheid van schema markup, FAQ blokken,
auteursinformatie, interne links en content lengte. Inclusief
groene/oranje/rode indicatoren per factor.

**Brand entity configuratie**
Stel je organisatie, social media profielen en adresgegevens in.
De plugin gebruikt deze data voor consistente entity markup
door je hele site.

= Waarom GEO? =

Meer dan 30% van de informatiezoekopdrachten eindigt nu bij een
AI-antwoord in plaats van een traditionele zoekresultatenpage.
Websites die niet GEO-geoptimaliseerd zijn missen deze zichtbaarheid
volledig. GEO Optimizer is de eerste WordPress plugin die zich
uitsluitend richt op AI-zoekmachine optimalisatie.

= Werkt samen met =

* RankMath SEO
* Yoast SEO
* All in One SEO
* Gutenberg editor
* Classic editor

= Over de ontwikkelaar =

GEO Optimizer is ontwikkeld door Erwin Verbeek, SEO-specialist met
20+ jaar ervaring en expert in GEO en AI-gedreven zoekmachine
optimalisatie. Meer informatie op
[erwinverbeek.nl/geo-optimizer](https://www.erwinverbeek.nl/geo-optimizer/).

== Installation ==

1. Upload de `geo-optimizer` map naar `/wp-content/plugins/`
2. Activeer de plugin via het 'Plugins' menu in WordPress
3. Ga naar Instellingen > GEO Optimizer
4. Vul je organisatiegegevens in op het tabblad "Organisatie"
5. Controleer je /llms.txt via jouwsite.nl/llms.txt

== Frequently Asked Questions ==

= Werkt deze plugin samen met RankMath of Yoast? =

Ja. GEO Optimizer detecteert automatisch of RankMath of Yoast
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

Ja. GEO Optimizer Pro bevat AI-powered content analyse,
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
* Eerste release
* Schema markup generator (Article, FAQPage, Organization, WebPage)
* llms.txt generator
* GEO Score meta box (0-100 per pagina)
* Brand entity configuratie
* Detectie en compatibiliteit met RankMath en Yoast SEO

== Upgrade Notice ==

= 1.0.0 =
Eerste release van GEO Optimizer.
