# Rispetto — pregled projekta

Centralni dokument za razumevanje celokupne Laravel aplikacije restorana **Rispetto Ristorante** (italijanska kuhinja, Batajnica, Beograd). Koristi se kao referenca pre izmena i dopuna.

---

## O projektu

| | |
|---|---|
| **Naziv** | Rispetto / Rispetto Ristorante |
| **Tip** | Javni marketing sajt + Filament admin panel |
| **Jezik** | Samo srpski (bez i18n) |
| **Valuta** | RSD |
| **Adresa** | Stevana Dubajića 5, Batajnica |
| **Telefon** | 063/72-88-155 |
| **Email** | rispettoristorante@office.com |
| **Radno vreme** | Pon–Sub 08:00–23:00, Nedelja neradan dan |
| **Partner dostave** | [Alideda](https://alideda.rs) |
| **Agencija** | [Frontrow Digital](https://frdigitalagency.com) |

**Status implementacije:** šema baze, migracije, modeli, Filament admin i javni sajt su završeni. Aktivni su lokalni TODO-ovi za mobilni layout i fine-tuning sekcija.

---

## Tech stack

| Sloj | Tehnologija |
|------|-------------|
| Backend | **Laravel 13** (`^13.7`), **PHP ^8.3** |
| Auth (starter kit) | **Laravel Fortify** + **Livewire 4** + **Flux 2** |
| Admin | **Filament 5** na `/admin` |
| Frontend build | **Vite 8** + `laravel-vite-plugin` |
| CSS | **Tailwind CSS v4** (`@tailwindcss/vite`) — bez `tailwind.config.js` |
| Baza | **SQLite** (dev) / **MySQL** (produkcija) |
| Testiranje | **Pest 4**, **PHPStan/Larastan**, **Laravel Pint** |
| Slike | **Sharp** (Node) za statične; **Laravel Chisel** + `StorageImageOptimizer` za upload |

### Ključne zavisnosti

**Composer:** `laravel/framework`, `filament/filament`, `livewire/livewire`, `livewire/flux`, `laravel/fortify`, `laravel/chisel`

**NPM:** `vite`, `tailwindcss`, `@tailwindcss/vite`, `@laravel/passkeys`, `sharp` (dev)

### Vite entry pointovi

| Fajl | Namena |
|------|--------|
| `resources/css/site.css` | Javni restoranski sajt |
| `resources/css/app.css` | Auth/dashboard (Flux) |
| `resources/js/site.js` | Mobilna navigacija (kritično) |
| `resources/js/defer.js` | Scroll reveal, testimonials carousel, katalog kategorija, pizza tabovi |
| `resources/js/app.js` | Starter kit app |
| `resources/js/passkeys.js` | Passkey autentifikacija |

### Fontovi

- **Outfit** — body (`font-sans`), Bunny CDN preko Vite
- **ITC Benguiat Std** — naslovi (`font-display`), lokalno u `public/fonts/benguiat/`
- **Instrument Sans** — auth/dashboard (`app.css`)

---

## Brzi start

```bash
composer setup          # install, migrate, npm build
composer dev            # serve + queue + pail + vite (concurrently)
composer test           # pint + phpstan + pest
npm run dev             # Vite dev server
npm run build           # produkcijski asseti
npm run optimize:images # generiše WebP + responsive varijante
php artisan db:seed     # admin korisnik + kategorije hrane
php artisan storage:link # za slike iz admina
```

### Admin pristup

- URL: `/admin`
- Email: `admin@rispetto.rs`
- Lozinka: `password` (promeniti u `.env` → `FILAMENT_ADMIN_*`)

---

## Arhitektura

```
Zahtev → routes/web.php → PageController → pages/*.blade.php
                                              ↓
                                    sections/* (po stranici)
                                              ↓
                                    x-site.* komponente
                                              ↓
                                    x-site.layout
```

**Principi:**
- Tanke page šablone u `resources/views/pages/`
- Sekcije u `resources/views/sections/` (po stranicama + `shared/`)
- Statički sadržaj i navigacija u `config/site.php`
- Dinamički sadržaj (jelovnik, pića) iz baze preko `PageController`
- Business logika u kontrolerima/support klasama — ne u Blade-u

---

## Rute

### Javni sajt

| URL | Ruta | Kontroler | Namena |
|-----|------|-----------|--------|
| `/` | `home` | `PageController@home` | Početna; sekcije iz `config('site.home_sections')` |
| `/jelovnik` | `menu` | `PageController@menu` | Jelovnik po kategorijama |
| `/karta-pica` | `drinks` | `PageController@drinks` | Pića po kategorijama |
| `/proslave` | `celebrations` | `PageController@celebrations` | Proslave i događaji |
| `/dostava` | `delivery` | `PageController@delivery` | Dostava i lično preuzimanje |
| `/o-nama` | `about` | `PageController@about` | O restoranu |
| `/sitemap.xml` | `sitemap` | `SitemapController` | XML sitemap |

### Auth / dashboard (starter kit)

| URL | Namena |
|-----|--------|
| `/dashboard` | Dashboard (auth + verified) |
| Fortify rute | Login, register, reset lozinke, 2FA, passkeys |

### Settings (`routes/settings.php`)

| URL | Namena |
|-----|--------|
| `/settings/profile` | Profil |
| `/settings/appearance` | Izgled |
| `/settings/security` | Bezbednost + passkeys |

### Filament admin

| URL | Namena |
|-----|--------|
| `/admin` | Admin panel |
| `/admin/qr-codes/{type}/download` | Preuzimanje QR kodova (menu, Instagram, Facebook) |

### Health check

- `/up` — Laravel health endpoint

---

## Struktura view-ova

### Layout

| Komponenta | Fajl | Namena |
|------------|------|--------|
| `x-site.layout` | `components/site/layout.blade.php` | HTML shell za sve javne stranice |
| `partials/site-head` | `partials/site-head.blade.php` | SEO meta, JSON-LD, Vite asseti |

### Site komponente (`components/site/`)

| Komponenta | Uloga |
|------------|-------|
| `header` / `footer` | Navigacija iz `config('site.navigation')`, kontakt, radno vreme |
| `container` | `max-w-[1400px]` ili `narrow` → `max-w-4xl` |
| `section` | Wrapper sekcije sa padding varijantama + scroll reveal |
| `optimized-image` | Responsive `<picture>` sa WebP srcset |
| `category-catalog` | Tabovi/dropdown za jelovnik i kartu pića |
| `menu-row` / `drink-row` | Pojedinačne stavke kataloga |
| `menu-pricing` / `item-weight` | Cene po veličini pice + gramaza |
| `featured-menu-card`, `menu-card`, `feature-card`, `testimonial-card` | Kartice sadržaja |
| `nav-link`, `logo`, `social-links`, `footer-brand` | UI elementi |

### Stranice i sekcije

| Stranica | View | Sekcije |
|----------|------|---------|
| Početna | `pages/home.blade.php` | Loop kroz `home_sections` |
| Jelovnik | `pages/menu.blade.php` | `shared.page-hero`, katalog, `shared.visit` |
| Karta pića | `pages/drinks.blade.php` | Isti pattern kao jelovnik |
| Proslave | `pages/celebrations.blade.php` | `celebrations/*`, `shared.visit` |
| Dostava | `pages/delivery.blade.php` | `delivery/*`, `shared.featured-menu`, testimonials, visit |
| O nama | `pages/about.blade.php` | `about/*`, testimonials, visit |

### Redosled sekcija na početnoj (`config/site.php` → `home_sections`)

1. `home.hero`
2. `home.features`
3. `shared.featured-menu`
4. `home.drinks-preview`
5. `home.celebrations-preview`
6. `shared.testimonials`
7. `shared.visit`

---

## Baza podataka

### Modeli

| Model | Tabela | Ključna polja |
|-------|--------|---------------|
| `Category` | `categories` | `name`, `slug`, `type` (food/drink), `sort_order` |
| `MenuItem` | `menu_items` | `category_id`, `name`, `description` (nullable), `weight`, `price` (nullable), `price_variants` (JSON), `image`, `featured`, `contents`, `sort_order`, `is_active` |
| `Drink` | `drinks` | `category_id` (nullable), `name`, `description`, `weight`, `price`, `image`, `contents`, `sort_order`, `is_active` |
| `Tag` | `tags` | `name`, `slug`, `type` (food/drink) |
| `User` | `users` | Standard + 2FA + passkeys |

### Pivot tabele

- `menu_item_tag` — MenuItem ↔ Tag (many-to-many)
- `drink_tag` — Drink ↔ Tag (many-to-many)

### Kategorije vs tagovi

- **Kategorije** — struktura menija (Predjela, Glavna jela, Dezerti…). Svaka stavka ima **jednu** kategoriju.
- **Tagovi** — filter oznake (Vegan, Ljuto, Bez glutena…). Stavka može imati **više** tagova.

### Pizza varijante

- Kategorija pice: slug `pizza` (`config/site.php` → `menu.pizza_category_slug`)
- Veličine: 25 cm, 35 cm, 50 cm — cene u `price_variants` JSON polju
- Frontend: tabovi u `defer.js` + `menu-pricing` komponenta

### Seeders

| Seeder | Sadržaj |
|--------|---------|
| `FilamentUserSeeder` | Admin korisnik |
| `CategorySeeder` | Predjela, Glavna jela, Dezerti (food) |
| `DatabaseSeeder` | Poziva oba gornja |

### Relacije

```php
// Category
hasMany(MenuItem::class);
hasMany(Drink::class);

// MenuItem
belongsTo(Category::class);
belongsToMany(Tag::class);

// Drink
belongsTo(Category::class);
belongsToMany(Tag::class);

// Tag
belongsToMany(MenuItem::class);
belongsToMany(Drink::class);
```

---

## Admin panel (Filament)

**Put:** `/admin`

### Resursi

| Resource | Namena |
|----------|--------|
| `CategoryResource` | CRUD kategorija, filter po food/drink |
| `TagResource` | CRUD tagova |
| `MenuItemResource` | Forma sa kategorijom, tagovima, slikom, price variants |
| `DrinkResource` | Forma sa kategorijom, tagovima, slikom |

### Custom stranice

- **QrCodes** — generisanje i preuzimanje brendiranih QR kodova (jelovnik, Instagram, Facebook)

### Upload slika

- Trait `OptimizesStorageImage` — automatski generiše responsive WebP varijante pri uploadu
- `StorageImageOptimizer` — obrada slika
- Artisan: `php artisan optimize:storage-images` — batch optimizacija

---

## Stilizovanje

### Dva odvojena sistema

**Javni sajt — `resources/css/site.css`**
- Tailwind v4 sa `@import 'tailwindcss'`
- Design tokeni u `@theme`:
  - `--color-rispetto-bg: #050505`
  - `--color-rispetto-accent: #00ff03`
  - `--color-rispetto-surface`, `elevated`, `border`, `text`, `muted`…
- Utility klase: `.site-heading`, `.site-btn-primary`, `.site-card`, `.site-panel`, `.site-section-padding`
- Respektuje `prefers-reduced-motion`

**Auth/dashboard — `resources/css/app.css`**
- Flux UI + Instrument Sans — **ne mešati** sa javnim sajtom

### Dizajn referenca

- Figma link i tokeni: `config/design.php`
- Cursor pravila: `.cursor/rules/rispetto-site.mdc`
- Lighthouse/WCAG: `.cursorrules` (rem jedinice, 48×48px touch targeti, eksplicitne dimenzije slika)

### Padding sekcija

| Klasa | Namena |
|-------|--------|
| `.site-section-padding` | Standardni vertikalni razmak sekcija |
| `.site-section-padding-hero` | Veći padding za hero sekcije |

Komponenta `x-site.section` automatski bira klasu na osnovu `$variant === 'hero'`.

---

## Slike i asseti

### Statične slike — `public/images/site/`

- Hero, about, celebrations, delivery, drinks promo
- Responsive varijante: `{ime}-640w.jpg`, `-1024w.jpg`, `-1280w.jpg` + `.webp`
- Ikone: `icons/tradition.svg`, `ingredients.svg`, `service.svg`
- Brand: `rispetto-logo.svg`, `partner-alideda.svg`, `og-image.jpg`

### Optimizacija

```bash
npm run optimize:images      # Sharp: WebP + width varijante
npm run optimize:seo-assets  # favicon, manifest, OG
```

### Komponenta za slike

Uvek koristiti `<x-site.optimized-image>` — nikad gol `<img>` na sajtu.

- Hero: `priority="true"` (LCP)
- Uvek: `alt`, `width`, `height`, `sizes`
- Konvencija: `hero-640w.webp`, `hero-1024w.webp`…

### PWA / SEO (`public/`)

- `favicon.ico`, `favicon.svg`, `apple-touch-icon.png`
- `icon-192.png`, `icon-512.png`, `site.webmanifest`
- `robots.txt` → dozvoljava sve, pokazuje na `/sitemap.xml`

---

## Konfiguracija

### `config/site.php` — glavni izvor istine

| Sekcija | Sadržaj |
|---------|---------|
| `seo` | Meta tagovi, OG, sitemap definicija |
| `phone`, `contact` | Kontakt podaci, Google Maps |
| `opening_hours` | Radno vreme |
| `navigation`, `footer_navigation` | Header i footer linkovi |
| `home_sections` | Redosled sekcija na početnoj |
| `features`, `testimonials` | Statički sadržaj kartica |
| `menu` | Pizza kategorija i veličine |
| `images` | Responsive widths, kvalitet |
| `social` | Društvene mreže |
| `qr` | Boje i dimenzije QR kodova |
| `partner`, `credits` | Alideda, Frontrow Digital |

### Env varijable

```env
APP_URL=
DB_CONNECTION=sqlite|mysql
FILAMENT_ADMIN_NAME=
FILAMENT_ADMIN_EMAIL=
FILAMENT_ADMIN_PASSWORD=
RISPETTO_FACEBOOK_URL=
RISPETTO_INSTAGRAM_URL=
RISPETTO_TIKTOK_URL=
```

---

## Backend logika

### Kontroleri

| Klasa | Uloga |
|-------|-------|
| `PageController` | Sve javne stranice; učitava kategorije/jela/pića |
| `SitemapController` | XML sitemap preko `SiteSeo::sitemapUrls()` |
| `Admin/QrCodeDownloadController` | SVG download QR kodova |

### Support klase

| Klasa | Uloga |
|-------|-------|
| `SiteSeo` | Meta tagovi, JSON-LD (Restaurant + WebPage), sitemap |
| `SiteImage` | Responsive srcset/WebP URL helperi |
| `StorageImageOptimizer` | Optimizacija uploadovanih slika |
| `RispettoQrCode` | Definicije QR kodova |
| `HasPriceVariants` (trait) | Logika cena po veličini pice |
| `OptimizesStorageImage` (trait) | Auto-optimizacija slika na model lifecycle |

### Frontend JS (`defer.js`)

- Scroll reveal animacije (`data-site-reveal`)
- Testimonials carousel
- Category catalog tabovi + mobilni dropdown
- Pizza size tab switching sa URL hash podrškom

---

## Testovi (Pest)

| Test | Pokriva |
|------|---------|
| `SitePagesTest` | Sve 6 javnih URL-ova vraćaju 200 |
| `SiteSeoTest` | SEO meta i sitemap |
| `MenuItemPricingTest` | Logika price variants |
| `SiteImageOptimizationTest` | Image helperi |
| `QrCodeTest` | QR generisanje |
| Auth/Settings testovi | Starter kit funkcionalnost |

---

## Struktura direktorijuma

```
rispetto/
├── app/
│   ├── Actions/Fortify/          # Registracija, reset lozinke
│   ├── Concerns/                 # HasPriceVariants, OptimizesStorageImage
│   ├── Console/Commands/         # OptimizeStorageImages
│   ├── Enums/                    # ProductType (food|drink)
│   ├── Filament/                 # Admin resursi + QrCodes stranica
│   ├── Http/Controllers/         # PageController, SitemapController
│   ├── Livewire/                 # Auth + Settings
│   ├── Models/                   # Category, MenuItem, Drink, Tag, User
│   ├── Providers/                # App, Fortify, Filament AdminPanel
│   └── Support/                  # SiteSeo, SiteImage, StorageImageOptimizer, RispettoQrCode
├── config/
│   ├── site.php                  # Glavna konfiguracija sajta
│   └── design.php                # Figma, boje, fontovi
├── database/
│   ├── migrations/               # Šema baze
│   └── seeders/                  # Admin + kategorije
├── public/
│   ├── images/site/              # Optimizovane statične slike
│   └── fonts/benguiat/           # Display font
├── resources/
│   ├── css/                      # site.css (javni), app.css (auth)
│   ├── js/                       # site.js, defer.js, passkeys.js
│   └── views/                    # Blade šabloni
├── routes/
│   ├── web.php                   # Javne rute
│   └── settings.php              # Auth settings
├── scripts/                      # optimize-site-images.mjs, generate-seo-assets.mjs
├── tests/                        # Pest testovi
├── .cursor/rules/                # rispetto-site.mdc
├── .cursorrules                  # Lighthouse/WCAG CSS pravila
├── README.md                     # Tehnička dokumentacija (šema, Filament)
├── mobile_responsive.md          # TODO za mobilni layout
├── qr_code.md                    # Napomene o QR implementaciji
└── PROJECT_OVERVIEW.md           # Ovaj fajl
```

---

## Otvoreni zadaci (TODO)

Izvor: `mobile_responsive.md`

### Mobilni layout

| Stranica | Zadatak | Status |
|----------|---------|--------|
| Početna — Hero | Na mobilnom: prvo tekst, pa slika ispod sa Google recenzijom | ⬜ |
| Footer | Kartica radnog vremena centralno na mobilnom/tabletu | ⬜ |
| Footer | Tekst "Vaše novo omiljeno mesto" poravnat od leve ivice | ⬜ |
| Proslave — radni dani | Prvo tekst, pa slika | ⬜ |
| Proslave — vikendom | Prvo tekst, pa slika | ⬜ |
| O nama — "Više o restoranu" | Prvo tekst, pa slika | ⬜ |

### Funkcionalnost (implementirano)

| Zadatak | Status |
|---------|--------|
| Pizza varijante (25/35/50 cm) sa posebnim cenama | ✅ |
| Opcioni opisi stavki | ✅ |
| Polje za gramazu (opciono) | ✅ |

---

## Trenutne lokalne izmene (necommitovano)

```
resources/css/site.css
  + .site-section-padding-hero (veći padding za hero sekcije)
  ~ .site-section-padding (smanjen lg padding)

resources/views/components/site/section.blade.php
  ~ hero varijanta koristi site-section-padding-hero
```

---

## Pravila razvoja

### CSS / Tailwind (`.cursorrules`)

1. **Font-size i mere** — samo `rem`/`em`, nikad `px` za fontove, padding, marginu
2. **Touch targeti** — min 48×48px (3rem) za interaktivne elemente
3. **Slike** — uvek `width` + `height` atributi ili `aspect-ratio`; `object-cover`
4. **Responsiveness** — fluidan layout (flex/grid), `max-w-*` umesto fiksnih širina

### Javni sajt (`.cursor/rules/rispetto-site.mdc`)

- Layout: `x-site.layout` + `x-site.container`
- Sekcije: `x-site.section` + podfolderi u `sections/`
- Navigacija: uvek iz `config('site.navigation')`
- Slike: uvek `<x-site.optimized-image>`
- Ne mešati `site.css` sa Flux/auth stilovima
- Tekst na srpskom

---

## Povezana dokumentacija

| Fajl | Sadržaj |
|------|---------|
| `README.md` | Šema baze, migracije, Filament setup, relacije |
| `mobile_responsive.md` | Mobilni layout TODO lista |
| `qr_code.md` | Napomene o QR kod implementaciji |
| `.cursor/rules/rispetto-site.mdc` | Cursor agent pravila za javni sajt |
| `.cursorrules` | Lighthouse/WCAG CSS ograničenja |
| `config/design.php` | Figma link, brand boje i fontovi |
| `public/fonts/benguiat/README.md` | Uputstvo za instalaciju fonta |

---

## Korisne komande

```bash
# Razvoj
composer dev
npm run dev

# Build i optimizacija
npm run build
npm run optimize:images

# Baza
php artisan migrate
php artisan db:seed
php artisan storage:link

# Kvalitet koda
composer test
composer lint
composer types:check

# Slike iz storage-a
php artisan optimize:storage-images
```

---

*Poslednje ažuriranje: jun 2026*
