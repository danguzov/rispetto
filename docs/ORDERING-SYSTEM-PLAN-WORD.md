# Rispetto — Plan sistema poručivanja

Verzija za Word / LibreOffice (bez Mermaid dijagrama).

Arhitektura: korpa na postojećem sajtu + Filament admin za kuhinju.

---

## 1. Pregled sistema

```
┌─────────────────────────────────────────────────────────────────────────┐
│                         rispetto.rs (isti Laravel projekat)             │
├──────────────────────────────┬──────────────────────────────────────────┤
│     JAVNI SAJT (kupac)       │         FILAMENT ADMIN (operater)        │
│                              │                                          │
│  /jelovnik  → Dodaj u korpu  │  /admin/kuhinja  → Live board (tablet)   │
│  /korpa     → Pregled korpe  │  /admin/porudzbine → Istorija, detalji   │
│  /narucivanje → Checkout     │  /admin (dashboard) → Toggle ON/OFF      │
│  /porudzbina/{token} → Status│                                          │
│                              │                                          │
│  Blade + site.css + Alpine   │  Filament + custom KitchenBoard page     │
└──────────────────────────────┴──────────────────────────────────────────┘
                                      │
                                      ▼
                    ┌─────────────────────────────────────┐
                    │  Laravel Reverb (WebSocket, besplatno) │
                    │  + Redis + Queue worker             │ 
                    └─────────────────────────────────────┘
```

### Potvrđene odluke

| Odluka | Vrednost |
|--------|----------|
| Plaćanje | Samo gotovina |
| Katalog v1 | Samo jela (MenuItem), bez pića |
| Kupac | Guest checkout (bez naloga) |
| Gde je korpa | Na postojećem sajtu (/jelovnik, /korpa…) |
| Gde je kuhinja | Filament /admin |
| Real-time | Laravel Reverb + Echo |
| Obaveštenja kupcu | Live stranica + Web Push (opt-in) + Email (opciono) |

---

## 2. Baza podataka — tabele i veze

### Veze između tabela

| Roditelj | Veza | Dete | Opis |
|----------|------|------|------|
| orders | 1 : N | order_items | Jedna porudžbina ima više stavki |
| orders | 1 : N | order_status_logs | Istorija promena statusa |
| menu_items | 1 : N | order_items | Referenca na jelo (snapshot u stavci) |
| users | 1 : N | order_status_logs | Admin koji je promenio status |
| restaurant_settings | — | — | Jedan red, nema FK veza |

### Tabela: restaurant_settings (jedan red — globalna podešavanja)

| Kolona | Tip | Opis |
|--------|-----|------|
| id | int | PK (uvek 1 red) |
| accepting_orders | boolean | Toggle iz admina — da li restoran prima nove porudžbine |
| closed_message | string, nullable | Poruka na sajtu kad je zatvoreno |
| delivery_enabled | boolean | Da li je dostava dostupna |
| pickup_enabled | boolean | Da li je lično preuzimanje dostupno |
| min_order_amount | decimal, nullable | Minimalna porudžbina (opciono) |
| updated_at | datetime | Poslednja izmena |

### Tabela: orders

| Kolona | Tip | Opis |
|--------|-----|------|
| id | int | PK |
| order_number | string, unique | Čitljiv broj: R-20260623-0042 |
| tracking_token | uuid, unique | Za URL /porudzbina/{token} |
| status | enum | Trenutni status (vidi sekciju 3) |
| fulfillment_type | enum | delivery ili pickup |
| customer_name | string | Ime kupca |
| phone | string | Telefon (obavezno) |
| email | string, nullable | Za email obaveštenje |
| address | string, nullable | Obavezno samo za delivery |
| notes | text, nullable | Napomena kupca |
| payment_method | string | Uvek cash u v1 |
| subtotal | decimal(10,2) | Zbir stavki |
| total | decimal(10,2) | = subtotal u v1 |
| estimated_ready_at | datetime, nullable | Postavlja kuhinja pri prihvatanju |
| accepted_at | datetime, nullable | Kada je prihvaćena |
| completed_at | datetime, nullable | Kada je završena |
| created_at | datetime | Vreme poručivanja |

### Tabela: order_items (snapshot — ne menja se ako se meni promeni)

| Kolona | Tip | Opis |
|--------|-----|------|
| id | int | PK |
| order_id | int | FK → orders.id |
| menu_item_id | int, nullable | FK → menu_items.id |
| name | string | Ime jela u trenutku poručivanja |
| variant_label | string, nullable | Npr. "35 cm" za picu |
| quantity | int | Količina |
| unit_price | decimal(10,2) | Cena po komadu |
| line_total | decimal(10,2) | quantity × unit_price |

### Tabela: order_status_logs (audit)

| Kolona | Tip | Opis |
|--------|-----|------|
| id | int | PK |
| order_id | int | FK → orders.id |
| user_id | int, nullable | FK → users.id |
| from_status | string, nullable | Prethodni status |
| to_status | string | Novi status |
| note | text, nullable | Napomena |
| created_at | datetime | Vreme promene |

### Postojeće tabele (bez izmena strukture)

| Tabela | Uloga u ordering sistemu |
|--------|--------------------------|
| menu_items | Izvor cena i naziva za korpu |
| categories | Grupisanje na /jelovnik |
| tags | Prikaz alergena (informativno) |
| users | Filament admin — upravlja porudžbinama |

### Session korpa (nije u bazi)

Korpa živi u Laravel session dok kupac ne završi checkout.

Primer (session key: "cart"):

- Stavka 1: menu_item_id=5, name=Margherita, variant_label=35 cm, unit_price=890.00, quantity=2

---

## 3. Statusi porudžbine

### Tok statusa (dijagram)

```
                    [KUPAC ZAVRŠI CHECKOUT]
                              │
                              ▼
                          pending
                         ╱      ╲
                        ╱        ╲
              Prihvati+ETA      Otkaži
                    │              │
                    ▼              ▼
               accepted       cancelled
              ╱      ╲              │
    U pripremi      Otkaži         │
            │          │           │
            ▼          ▼           │
       preparing  cancelled        │
            │                      │
       Spremno                     │
            │                      │
            ▼                      │
          ready                    │
         ╱     ╲                   │
   pickup    delivery              │
       │         │                   │
       │         ▼                   │
       │   out_for_delivery         │
       │         │                   │
       ▼         ▼                   ▼
   completed  completed         [KRAJ]
```

### Tabela statusa

| Status | Značenje | Ko menja | Kupac vidi |
|--------|----------|----------|------------|
| pending | Nova porudžbina, čeka potvrdu | Automatski pri submit-u | Čekamo potvrdu restorana |
| accepted | Restoran prihvatio + ETA postavljen | Admin u KitchenBoard | Prihvaćeno — spremno za ~45 min |
| preparing | U pripremi u kuhinji | Admin | Vaše jelo se priprema |
| ready | Spremno | Admin | Pickup: Dođite po porudžbinu |
| out_for_delivery | Na putu | Admin (samo delivery) | Na putu ka vama |
| completed | Završeno | Admin | Prijatno! |
| cancelled | Otkazano | Admin | Porudžbina otkazana |

---

## 4. Rute — kompletna mapa

### Javni sajt (kupac)

| Metoda | Ruta | Kontroler | Namena |
|--------|------|-----------|--------|
| GET | /jelovnik | PageController@menu | Postojeći meni + dugme Dodaj |
| POST | /korpa/dodaj | CartController@add | Dodavanje stavke (AJAX) |
| PATCH | /korpa/stavka/{key} | CartController@update | Promena količine |
| DELETE | /korpa/stavka/{key} | CartController@remove | Uklanjanje stavke |
| GET | /korpa | CartController@show | Stranica korpe |
| GET | /narucivanje | CheckoutController@show | Checkout forma |
| POST | /narucivanje | CheckoutController@store | Kreiranje porudžbine |
| GET | /porudzbina/{token} | OrderTrackingController@show | Praćenje statusa |

Middleware EnsureOrderingOpen blokira POST /korpa/dodaj i POST /narucivanje kad je accepting_orders = false.

### Filament admin (operater)

| Putanja | Tip | Namena |
|---------|-----|--------|
| /admin | Dashboard | Widget: toggle Primamo porudžbine |
| /admin/kuhinja | Custom Page (KitchenBoard) | Live tabla sa karticama porudžbina |
| /admin/porudzbine | Resource (OrderResource) | Lista, filteri, detalji, istorija |
| /admin/porudzbine/{id} | Resource View | Puna istorija statusa |
| /admin/menu-items | Postojeći Resource | CMS menija (bez izmena) |

---

## 5. Tokovi — korak po korak

### 5.1 Kupac poručuje (happy path)

| Korak | Ko | Akcija |
|-------|-----|--------|
| 1 | Kupac | Otvara /jelovnik |
| 2 | Kupac | Klikne Dodaj (za picu: izabere veličinu) |
| 3 | Sajt | Ažurira session korpu |
| 4 | Sajt | Prikazuje badge u headeru (broj stavki) |
| 5 | Kupac | Otvara /korpa, klikne Naruči |
| 6 | Kupac | Na /narucivanje unosi ime, telefon, adresu, dostava/pickup |
| 7 | Kupac | Šalje POST /narucivanje |
| 8 | Sajt | Upisuje orders + order_items u bazu |
| 9 | Sajt | Šalje OrderCreated preko Reverb-a |
| 10 | KitchenBoard | Prima porudžbinu — zvuk + nova kartica |
| 11 | Sajt | Preusmerava kupca na /porudzbina/{token} |
| 12 | Admin | Prihvata porudžbinu + postavlja ETA (npr. 45 min) |
| 13 | Sajt | Šalje OrderStatusUpdated preko Reverb-a |
| 14 | Kupac | Vidi Prihvaćeno + countdown na tracking stranici |
| 15 | Sajt | Šalje email (ako unet) ili Web Push (ako opt-in) |

### 5.2 Admin zatvara prijem porudžbina

| Korak | Ko | Akcija |
|-------|-----|--------|
| 1 | Admin | U Filamentu pali toggle OFF — Primamo porudžbine |
| 2 | Filament | Upisuje restaurant_settings.accepting_orders = false |
| 3 | Sajt | Prikazuje banner: Trenutno ne primamo porudžbine |
| 4 | Sajt | POST /korpa/dodaj vraća grešku 403 |
| 5 | Sajt | Dugmad Dodaj su disabled na jelovniku |

### 5.3 Kuhinja obrađuje porudžbinu

```
Nova porudžbina (pending)
        │
        ▼
  [Prihvati] → modal: ETA (30/45/60/custom min)
        │
        ▼
   accepted (+ estimated_ready_at)
        │
        ▼
  [U pripremi] → preparing
        │
        ▼
  [Spremno] → ready
        │
        ├── pickup ──► [Završeno] → completed
        │
        └── delivery ──► [Na putu] → out_for_delivery ──► [Završeno] → completed
```

---

## 6. Real-time — događaji i kanali

### Broadcasting tabela

| Event | Kanal | Ko sluša | Šta se dešava |
|-------|-------|----------|---------------|
| OrderCreated | kitchen.orders (private) | KitchenBoard u Filamentu | Nova kartica + zvuk |
| OrderStatusUpdated | order.{tracking_token} (public) | /porudzbina/{token} | Status + ETA ažurirani uživo |
| OrderingAvailabilityChanged | site.ordering (public) | Banner na sajtu (opciono) | ON/OFF toggle reflektovan |

### Infrastruktura

| Komponenta | Uloga | Produkcija |
|------------|-------|------------|
| Laravel Reverb | WebSocket server | php artisan reverb:start (supervisor) |
| Redis | Reverb backend + queue | Obavezan na produkciji |
| Queue worker | Email, Web Push | php artisan queue:work |
| Echo + pusher-js | Frontend klijent | KitchenBoard + tracking stranica |

Fallback: wire:poll.5s na KitchenBoard ako WebSocket padne.

---

## 7. Frontend — šta se menja na sajtu

### Nove / izmenjene komponente

| Fajl | Promena |
|------|---------|
| components/site/menu-row.blade.php | + dugme Dodaj u korpu + pizza size picker |
| components/site/featured-menu-card.blade.php | + quick-add dugme |
| components/site/header.blade.php | + ikona korpe sa badge-om |
| components/site/ordering-banner.blade.php | NOVO — banner kad je restoran zauzet |
| components/site/cart-summary.blade.php | NOVO — pregled stavki |
| pages/cart.blade.php | NOVO — /korpa |
| pages/checkout.blade.php | NOVO — /narucivanje |
| pages/order-tracking.blade.php | NOVO — /porudzbina/{token} |
| js/defer-cart.js | NOVO — AJAX korpa, badge update |
| css/site.css | + klase .site-cart-* |

### Koje stranice učitavaju cart JS

| Ruta | defer-cart.js | Razlog |
|------|---------------|--------|
| / (home) | Da | Featured jela |
| /jelovnik | Da | Glavni katalog |
| /dostava | Da | Featured jela |
| /korpa, /narucivanje | Da | Korpa/checkout |
| /porudzbina/* | Ne (Echo umesto) | Samo tracking |
| /o-nama, /proslave | Ne | Nema korpe |

---

## 8. Filament — struktura admin dela

```
/admin
├── Dashboard
│   └── Widget: AcceptingOrdersToggle (Primamo porudžbine ON/OFF)
│
├── Kuhinja (custom page: KitchenBoard)
│   ├── Live kartice: pending | accepted | preparing | ready
│   ├── Zvuk na novu porudžbinu
│   ├── Akcija: Prihvati → ETA modal
│   ├── Akcije: U pripremi | Spremno | Na putu | Završeno | Otkaži
│   └── Echo listener + poll fallback
│
├── Porudžbine (OrderResource)
│   ├── Lista sa filterima (status, datum, tip)
│   ├── View: detalji kupca, stavke, istorija
│   └── Edit statusa (za edge slučajeve)
│
└── Jelovnik (postojeći MenuItemResource — bez izmena)
```

### KitchenBoard — izgled (koncept)

```
┌─────────────────────────────────────────────────────────────────┐
│  PRIMAMO PORUDZBINE  [━━━━ ON ━━━━]              3 aktivne      │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌─ NOVA ─────────────┐  ┌─ U PRIPREMI ────────┐               │
│  │ #R-0623-0042       │  │ #R-0623-0040       │               │
│  │ Dostava            │  │ Preuzimanje         │               │
│  │ Marko · 063...      │  │ Ana · 064...        │               │
│  │ 2× Margherita 35cm  │  │ 1× Carbonara        │               │
│  │ 1× Tiramisu         │  │ ETA: 12 min         │               │
│  │ Ukupno: 2.340 RSD   │  │ [Spremno]           │               │
│  │ [Prihvati] [Otkaži] │  └─────────────────────┘               │
│  └─────────────────────┘                                       │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## 9. Obaveštenja kupcu

| Kanal | Kada se šalje | Uslov | Cena |
|-------|---------------|-------|------|
| Live stranica (/porudzbina/{token}) | Svaka promena statusa | Kupac ima otvoren link | Besplatno |
| Web Push | Prihvatanje + promene statusa | Kupac kliknuo Obavesti me | Besplatno (VAPID) |
| Email | Pri prihvatanju sa ETA | Kupac uneo email | Besplatno (SMTP) |
| SMS | — | Nije u v1 | Plaćeno |

### Tracking stranica — šta kupac vidi

```
┌─────────────────────────────────────────┐
│  Porudžbina #R-20260623-0042            │
│                                         │
│  Primljena          18:32               │
│  Prihvaćena           18:34               │
│  ●  U pripremi          sada            │
│  ○  Spremno                             │
│  ○  Na putu                             │
│                                         │
│  Spremno za ~18 min (18:52)             │
│                                         │
│  [Obavesti me]   [QR kod linka]         │
│                                         │
│  Plaćanje: gotovinom pri dostavi        │
└─────────────────────────────────────────┘
```

---

## 10. Servisi i odgovornosti (backend)

| Servis / Klasa | Odgovornost |
|----------------|-------------|
| CartService | Session korpa: add, update, remove, total, validate |
| OrderService | Kreiranje porudžbine iz korpe, snapshot cena, generisanje broja |
| RestaurantSettingsService | Čitanje/caching toggle-a, da li je otvoreno |
| CartController | HTTP za AJAX operacije nad korpom |
| CheckoutController | Prikaz forme + submit porudžbine |
| OrderTrackingController | Tracking stranica za kupca |
| EnsureOrderingOpen | Middleware — blokira kad je restoran zauzet |
| OrderCreated (Event) | Broadcast ka kuhinji |
| OrderStatusUpdated (Event) | Broadcast ka kupcu |
| SendOrderAcceptedNotification (Job) | Email kupcu |

---

## 11. Validacije

### Pri dodavanju u korpu

| Pravilo | Poruka |
|---------|--------|
| menu_item_id postoji i is_active = true | — |
| Pizza mora imati variant_label | Izaberite veličinu pice |
| quantity 1–20 | — |
| accepting_orders = true | Restoran trenutno ne prima porudžbine |

### Pri checkout-u

| Polje | Pickup | Delivery |
|-------|--------|----------|
| customer_name | obavezno | obavezno |
| phone | obavezno | obavezno |
| address | — | obavezno |
| email | opciono | opciono |
| fulfillment_type | pickup | delivery |
| Korpa nije prazna | obavezno | obavezno |
| total >= min_order_amount | ako je podešeno | ako je podešeno |

---

## 12. Faze implementacije

| Faza | Trajanje | Šta se radi | Rezultat |
|------|----------|-------------|----------|
| A — Temelj | 1–2 dana | Migracije, modeli, servisi, rute, middleware | Backend spreman |
| B — Korpa na sajtu | 2 dana | Dodaj u korpu, header badge, /korpa, /narucivanje | Kupac može da poruči |
| C — Filament | 1–2 dana | OrderResource, KitchenBoard, toggle | Admin upravlja porudžbinama |
| D — Live | 1–2 dana | Reverb, Echo, zvuk, ETA modal | Sve radi uživo |
| E — Polish | 1 dan | Web Push, email, banner, testovi, deploy docs | Produkcija spremna |

Ukupno: ~6–9 radnih dana

---

## 13. Produkcijski zahtevi

| Proces | Komanda | Obavezan |
|--------|---------|----------|
| Web server | Nginx + PHP-FPM | Da |
| WebSocket | php artisan reverb:start | Da |
| Queue | php artisan queue:work | Da (email/push) |
| Redis | REDIS_HOST=127.0.0.1 | Da |
| Nginx WS proxy | Upgrade header za Reverb port | Da |

### .env varijable (nove)

BROADCAST_CONNECTION=reverb

REVERB_APP_ID=rispetto

REVERB_APP_KEY=...

REVERB_APP_SECRET=...

REVERB_HOST=ws.rispetto.rs

REVERB_PORT=443

REVERB_SCHEME=https

QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1

VAPID_PUBLIC_KEY=...

VAPID_PRIVATE_KEY=...

---

## 14. Šta NIJE u prvoj verziji

| Feature | Razlog | Kada |
|---------|--------|------|
| Online plaćanje | Odluka — samo gotovina | Faza 2+ |
| Pića u korpi | Odluka — prvo jela | Faza 2 |
| Kupac nalog | Guest checkout dovoljan | Po potrebi |
| SMS obaveštenja | Nije besplatno | Po potrebi |
| Zona dostave / geofencing | Kompleksnost | Faza 2 |
| Odvojeni /app | Zamenjeno integrisanim pristupom | — |
| Poseban kitchen login | Koristi postojeći Filament | — |

---

## 15. Rizici

| Rizik | Verovatnoća | Mitigacija |
|-------|-------------|------------|
| Reverb zahteva Redis + dodatni proces | Srednja | Supervisor, deploy docs |
| Filament tabela loša za tablet kuhinju | Visoka ako se forsira table | Custom KitchenBoard page |
| Cart JS usporava sajt | Niska | defer-cart.js samo na relevantnim rutama |
| Admin zaboravi toggle OFF | Srednja | Vidljiv widget na Dashboard-u |
| WebSocket padne | Niska | wire:poll.5s fallback |
| Cene se promene tokom poručivanja | Niska | Snapshot u order_items |

---

## 16. Veza sa postojećim kodom

| Postojeći kod | Novi kod |
|---------------|----------|
| MenuItem model | CartService čita cene |
| SitePageCache | Isti katalog na /jelovnik |
| menu-row.blade.php | + dugme Dodaj |
| featured-menu-card | + quick-add |
| header.blade.php | + cart badge |
| PageController | Bez izmena |
| Filament MenuItemResource | Bez izmena |
| AdminPanelProvider | + discoverPages (KitchenBoard) |
| delivery/options.blade | Kartica → /jelovnik |
| config/site.php | + ordering.enabled, nav link |

---

Dokument za projekat Rispetto — sistem poručivanja v1.

Word-friendly verzija — generisano za kopiranje u Word / LibreOffice Writer.
