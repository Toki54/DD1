#!/bin/bash
set -e

# Migrations automatiques au démarrage
php bin/console doctrine:migrations:migrate --no-interaction --env=prod

# Vider le cache
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod

# Lancer Apache
apache2-foreground
```

### 2. Créer le service sur Render

1. Va sur [render.com](https://render.com) → **New → Web Service**
2. Connecte ton repo GitHub
3. Choisis **Docker** comme environment
4. Crée aussi une **PostgreSQL database** sur Render (New → PostgreSQL)
5. Render te donnera une `DATABASE_URL` au format : `postgresql://user:pass@host/dbname`

### 3. Variables d'environnement sur Render

Dans ton service Render → **Environment** → ajoute toutes ces variables :
```
APP_ENV=prod
APP_SECRET=génère_une_clé_aléatoire_32_chars
DATABASE_URL=postgresql://... (donnée par Render)
MAILER_DSN=smtp://ton_user:ton_pass@mail.parlottes.com:587
MAILER_FROM=noreply@parlottes.com
APP_URL=https://parlottes.com

STRIPE_PUBLIC_KEY=pk_live_...
STRIPE_SECRET_KEY=sk_live_...
STRIPE_WEBHOOK_SECRET=whsec_...
STRIPE_PRICE_WEEK=price_...
STRIPE_PRICE_MONTH=price_...
STRIPE_PRICE_3MONTHS=price_...
STRIPE_PRICE_6MONTHS=price_...
STRIPE_PRICE_YEAR=price_...
STRIPE_PRICE_LIFETIME=price_...

PUSHER_APP_ID=
PUSHER_KEY=
PUSHER_SECRET=

RECAPTCHA_SITE_KEY=6Ldsj0Us...
RECAPTCHA_SECRET_KEY=6Ldsj0Us...