# Rispetto — restoran


**Filament admin** (`/admin`):
- Email: `admin@rispetto.rs`
- Lozinka: `password` (promeni u `.env` → `FILAMENT_ADMIN_*`)
- Seed: `php artisan db:seed --class=FilamentUserSeeder`

Laravel aplikacija za restoran **Rispetto**: jelovnik, piće, admin panel, online poručivanje i javni sajt.

## Online poručivanje

- Kupac: `/jelovnik` → korpa → `/narucivanje` → `/porudzbina/{token}`
- Admin: `/admin/kuhinja` (kitchen board), `/admin/porudzbine`
- Produkcija: [docs/ORDERING-DEPLOY.md](docs/ORDERING-DEPLOY.md)
- Plan: [docs/ORDERING-SYSTEM-PLAN.md](docs/ORDERING-SYSTEM-PLAN.md)

## Stack

- **Laravel 13** + Livewire (starter kit)
- **Filament** — admin panel (CRUD za jelovnik, piće, kategorije, tagove)
- **Baza** — MySQL / SQLite (dev)

## Entiteti

| Model | Opis |
|-------|------|
| `Category` | Kategorije jela (Predjela, Glavna jela, Dezerti…) i po potrebi pića |
| `MenuItem` | Stavke jelovnika |
| `Drink` | Pića |
| `Tag` | Oznake za filtriranje (Vegan, Ljuto, Domaće…) — **ne** zamenjuju kategorije |

### Kategorije vs tagovi

- **Kategorije** — struktura menija: *Predjela*, *Glavna jela*, *Dezerti*… Svaka stavka jelovnika pripada **jednoj** kategoriji.
- **Tagovi** — dodatne oznake koje se mogu kombinovati: *Vegan*, *Bez glutena*, *Ljuto*… Jedna stavka može imati **više** tagova.

Pića mogu imati sopstvene kategorije (npr. *Pivo*, *Vino*, *Kokteli*) u istoj `categories` tabeli, razlikovane kolonom `type`.

---

## Šema baze

### `categories`

| Kolona | Tip | Napomena |
|--------|-----|----------|
| `id` | bigint | PK |
| `name` | string | npr. „Predjela”, „Glavna jela” |
| `slug` | string | unique, za URL |
| `type` | enum | `food` \| `drink` |
| `sort_order` | unsigned int | redosled prikaza, default 0 |
| `timestamps` | | |

### `menu_items` (jelovnik)

| Kolona | Tip | Napomena |
|--------|-----|----------|
| `id` | bigint | PK |
| `category_id` | foreignId | → `categories` (type = food) |
| `name` | string | ime |
| `description` | text | opis |
| `price` | decimal(10,2) | cena (RSD) |
| `image` | string, nullable | putanja u storage |
| `featured` | boolean | istaknuto na sajtu, default false |
| `contents` | text, nullable | šta sve ide — sastojci / napomena |
| `sort_order` | unsigned int | redosled unutar kategorije, default 0 |
| `is_active` | boolean | sakrij bez brisanja, default true |
| `timestamps` | | |

### `drinks` (piće)

| Kolona | Tip | Napomena |
|--------|-----|----------|
| `id` | bigint | PK |
| `category_id` | foreignId, nullable | → `categories` (type = drink) |
| `name` | string | ime |
| `description` | text, nullable | opis (opciono) |
| `price` | decimal(10,2) | cena (RSD) |
| `image` | string, nullable | putanja u storage |
| `contents` | text, nullable | šta sve ide — sastojci / napomena |
| `sort_order` | unsigned int | default 0 |
| `is_active` | boolean | default true |
| `timestamps` | | |

### `tags`

Jedna tabela za tagove hrane i pića — **ne** pravi se odvojena migracija po tipu.

| Kolona | Tip | Napomena |
|--------|-----|----------|
| `id` | bigint | PK |
| `name` | string | npr. „Vegan”, „Ljuto” |
| `slug` | string | unique |
| `type` | enum | `food` \| `drink` — koji proizvodi mogu nositi tag |
| `timestamps` | | |

### Pivot tabele

```
menu_item_tag   menu_item_id, tag_id   (unique na paru)
drink_tag       drink_id, tag_id       (unique na paru)
```

---

## Redosled migracija

```
1. create_categories_table
2. create_tags_table
3. create_menu_items_table
4. create_drinks_table
5. create_menu_item_tag_table
6. create_drink_tag_table
```

Jedna migracija po tabeli — standardno za Laravel, lak rollback i pregled.

---

## Filament (admin)

Instalacija:

```bash
composer require filament/filament:"^5.0" -W
php artisan filament:install --panels
php artisan storage:link
```

Admin panel: `/admin` — korisnik iz `FilamentUserSeeder` (vidi gore).

Resursi (redosled implementacije):

1. **CategoryResource** — CRUD kategorija, filter po `type` (food / drink)
2. **TagResource** — CRUD tagova, filter po `type`
3. **MenuItemResource** — forma, izbor kategorije, dodela tagova (food)
4. **DrinkResource** — forma, izbor kategorije, dodela tagova (drink)

Tagovi i kategorije se dodaju u adminu; proizvodima se dodeljuju kategorija + tagovi, a u polje `contents` se unosi posebno šta sve ide u stavku.

---

## Struktura fajlova

```
app/Models/
  Category.php
  MenuItem.php
  Drink.php
  Tag.php

app/Filament/Resources/
  CategoryResource.php
  MenuItemResource.php
  DrinkResource.php
  TagResource.php
```

### Relacije u modelima

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

## Plan rada

| Korak | Akcija | Status |
|-------|--------|--------|
| 1 | Potvrditi šemu | ✅ |
| 2 | Migracije + modeli + relacije | ✅ |
| 3 | Instalacija Filament + Resource-i | ✅ |
| 4 | Javni sajt — šabloni, sekcije, jelovnik/piće iz baze | ✅ |

---

## Javni sajt

| Stranica | URL |
|----------|-----|
| Početna | `/` |
| Jelovnik | `/jelovnik` |
| Karta pića | `/karta-pica` |
| Proslave | `/proslave` |
| O nama | `/o-nama` |

- **Sekcije:** `resources/views/sections/` — po stranicama (`home/`, `about/`, …) i zajedničke (`shared/`)
- **Layout:** `x-site.layout`, kontejner `x-site.container` (`max-w-7xl`)
- **Slike:** `<x-site.optimized-image>` + `npm run optimize:images` za statične slike u `public/images/site/`
- **Dizajn (Figma):** pozadina `#050505`, akcent `#00FF03`, fontovi ITC Benguiat Std + Outfit — `config/design.php`
- **Pravila razvoja:** `.cursor/rules/rispetto-site.mdc`

---

## Odluke

- **Slike** — jedna slika po stavci
- **Cena** — uvek u RSD (`decimal(10,2)`)
- **Jezik** — samo srpski, bez i18n
- **Seed kategorije** — Predjela, Glavna jela, Dezerti (`CategorySeeder`)
