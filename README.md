# SkiJumping Flight Lab

New Laravel 13 / Filament 5 API to store simulated and actual ski jumps and reuse successful flight strategies.

## Environment
Use APP_NAME, APP_ENV, APP_KEY, APP_DEBUG, APP_URL, DB_CONNECTION=mysql, DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD, SESSION_DRIVER=database, CACHE_STORE=database, QUEUE_CONNECTION=database. Never commit .env.

## Requirements
PHP 8.3+, Composer 2, MySQL 8.0+ with PDO MySQL (SQLite supported for tests), required PHP extensions, and HTTPS for Chrome-extension API access.

## Installation
```bash
composer install
cp docs/environment.template .env
php artisan key:generate
# Configure DB_* in .env and create the MySQL database
php artisan migrate
php artisan make:filament-user
php artisan serve
```

Admin panel: `/admin`. API: `/api/v1`.

Create an API token for your administrator account via Tinker:
```php
App\Models\User::firstOrFail()->createToken('chrome-extension', ['flight:write','flight:read'])->plainTextToken;
```
Keep the token in extension local storage (never include in Git); API requires `Authorization: Bearer <token>`. Rotate/revoke if compromised. Configure CORS explicitly for your extension origin and use HTTPS in production.

## API
- `POST /api/v1/sessions`: `{hill,gate,physics_version,wind:{mean,gust,seed,phase},rho,mu}`.
- `POST /api/v1/strategies`: `{hill,physics_version,controls:{...}}`.
- `POST /api/v1/simulations/batch`: `{session_id,items:[{strategy_id,distance,telemark,fall,metrics}]}` (max 200 items).
- `POST /api/v1/jumps`: `{session_id,simulation_id,strategy_id,distance,telemark,fall,telemetry}`.
- `GET /api/v1/strategies/best?hill=akureyri&gate=26&physics_version=...&limit=3`: successful historic candidates, sorted by simulation distance, including `controls` and `wind: {mean,gust,seed,phase}` from the corresponding flight session. Always re-simulate under current wind before replay. The API returns **your own** saved simulations, not the game's global leaderboard players.
- `GET /api/v1/stats`: aggregated counts and best distance.

Write operations use a token with `flight:write`; read operations use `flight:read`. Raw ticket tokens/cookies are never accepted or stored.

## Operational notes
Each session records the effective wind phase **at takeoff**, rather than just ticket windT0; physics versions isolate incompatible histories. Real jumps reference their source simulation. The optimization engine remains in the Chrome extension for this MVP. Data ingestion does **not** itself train a model: the API supplies previously successful strategies as seeds for future simulations.

Do not commit credentials or game session tickets. Back up MySQL; run `php artisan migrate --force` during deployment. Production workers/queues can be added for telemetry compression and maintenance.

## MySQL deployment

Create an empty MySQL 8.0+ database and a dedicated account with privileges limited to this database. Copy `docs/environment.template` to `.env`, configure `DB_CONNECTION=mysql`, port `3306` and the server credentials, then run `php artisan migrate --force`. Back up MySQL regularly. Tests run against in-memory SQLite by default; check migrations on your MySQL staging instance before deploying to production. **Never run `migrate:fresh` on production data.**

## Strict wind-matched automatic attempts

The opt-in Chrome extension v0.3.1 uses historical Flight Lab TOP 3 reference winds. It requires current mean wind within ±0.35 m/s, gust within ±0.25 m/s and instantaneous wind at the hill within ±0.50 m/s of a stored reference, simultaneously. If no eligible reference exists or no match occurs within 180 seconds, the extension does not jump. This is a heuristic compatibility gate, not a guarantee of identical in-flight conditions or a new record. Full global record holder replays require public replay records and verification of their metadata; their private trajectories must not be inferred from the leaderboard's distances.
