# Ordering sistem — produkcijski deploy

Vodič za pokretanje online poručivanja na produkciji (Rispetto).

## Pregled procesa

| Proces | Komanda | Obavezno |
|--------|---------|----------|
| Web server | Nginx + PHP-FPM | Da |
| WebSocket | `php artisan reverb:start` | Da |
| Queue worker | `php artisan queue:work` | Da (email, push) |
| Redis | `REDIS_HOST=127.0.0.1` | Da |
| Scheduler | `php artisan schedule:work` | Po potrebi |

## 1. .env varijable

### Launch bez online poručivanja (samo vidljiv sajt)

Dok javni ordering nije spreman za korisnike, postavite:

```env
ORDERING_ENABLED=false
```

Posle promene obavezno:

```bash
php artisan config:cache
```

**Šta se dešava:**
- Javni sajt (početna, jelovnik, pića, dostava) radi normalno — bez korpe i „Dodaj u korpu“
- `/korpa` i `/narucivanje` vraćaju 404
- POST mutacije (dodavanje u korpu, slanje porudžbine) su blokirane
- **Admin** (`/admin`, kitchen board) radi nezavisno od ovog flaga
- Reverb, queue worker i Redis **nisu potrebni** dok je ordering isključen za posetioce

### Pun ordering (kada budete spremni)

```env
ORDERING_ENABLED=true

BROADCAST_CONNECTION=reverb
REVERB_APP_ID=rispetto
REVERB_APP_KEY=...
REVERB_APP_SECRET=...
REVERB_HOST=ws.rispetto.rs
REVERB_PORT=443
REVERB_SCHEME=https

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"

QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=...
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_FROM_ADDRESS=noreply@rispetto.rs
MAIL_FROM_NAME="Rispetto"

VAPID_SUBJECT=mailto:rispettoristorante@office.com
VAPID_PUBLIC_KEY=...
VAPID_PRIVATE_KEY=...
```

VAPID ključeve generišite lokalno:

```bash
./vendor/bin/web-push generate
```

## 2. Deploy koraci

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

## 3. Supervisor — Reverb

`/etc/supervisor/conf.d/rispetto-reverb.conf`:

```ini
[program:rispetto-reverb]
process_name=%(program_name)s
command=php /var/www/rispetto/artisan reverb:start
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/rispetto-reverb.log
stopwaitsecs=3600
```

## 4. Supervisor — Queue worker

`/etc/supervisor/conf.d/rispetto-queue.conf`:

```ini
[program:rispetto-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/rispetto/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/log/rispetto-queue.log
stopwaitsecs=3600
```

Nakon izmene:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start rispetto-reverb rispetto-queue:*
```

## 5. Nginx — WebSocket proxy za Reverb

Primer lokacije za `wss://ws.rispetto.rs` → lokalni Reverb (`127.0.0.1:8080`):

```nginx
server {
    listen 443 ssl http2;
    server_name ws.rispetto.rs;

    ssl_certificate     /etc/letsencrypt/live/ws.rispetto.rs/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/ws.rispetto.rs/privkey.pem;

    location / {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Host $http_host;
        proxy_set_header Scheme $scheme;
        proxy_set_header SERVER_PORT $server_port;
        proxy_set_header REMOTE_ADDR $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "Upgrade";
    }
}
```

Glavni sajt (`rispetto.rs`) ostaje na standardnom PHP-FPM virtual hostu.

## 6. Provera nakon deploy-a

1. **Sajt:** `/jelovnik` → dodaj u korpu → `/korpa` → `/narucivanje` → porudžbina kreirana
2. **Admin:** `/admin/kuhinja` → nova porudžbina se pojavi (Echo ili poll)
3. **Toggle:** Dashboard → „Primamo porudžbine” OFF → banner na sajtu + disabled dugmad
4. **Email:** prihvati porudžbinu sa emailom → proveri inbox (ili log)
5. **Push:** na tracking stranici „Obavesti me” → promeni status u kuhinji
6. **Queue:** `php artisan queue:monitor` ili log bez grešaka

## 7. Korisne komande

```bash
# Lokalni dev (sve odjednom)
composer dev

# Samo Reverb
php artisan reverb:start

# Samo queue
php artisan queue:work

# Testovi ordering modula
./vendor/bin/pest tests/Feature/Ordering
```

## 8. Rutе reference

| Ruta | Opis |
|------|------|
| `/jelovnik` | Katalog + dodaj u korpu |
| `/korpa` | Pregled korpe |
| `/narucivanje` | Checkout |
| `/porudzbina/{token}` | Tracking za kupca |
| `/dostava` | CTA za online poručivanje |
| `/admin` | Filament admin |
| `/admin/kuhinja` | Kitchen board |
| `/admin/porudzbine` | Lista porudžbina |

---

*Povezano: [ORDERING-SYSTEM-PLAN.md](./ORDERING-SYSTEM-PLAN.md)*
