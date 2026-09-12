#LAND OF LITIGATION#

This is a **VERY OLD** game I made, back in 2005.  It was my first PHP project ever!

I made this for CS 4-something, Distributed Systems.  The project was pretty open ended, we had to "propose, design, and make a dynamic web page."  Over about 5 weeks, this is what I came up with (while simultaniously teaching myself PHP, mysql, and programming in general).  I got an A.

It's pretty horrible.  I've had dreams of fixing it up for some time now.  Let's see what happens.

## Running the PHP 8.5 / MySQL 8.4 version

This compatibility migration keeps the original pages, CSS, images, classes, and
combat formulas. It uses PDO MySQL with native prepared statements and PHP password
hashes. It requires a fresh database; old player accounts and progress are not imported.

With Docker Desktop running and Docker Compose installed:

- [ ] Copy `.env.example` to `.env`. The example passwords are for local development only.
- [ ] Run `docker compose -p lol-dev up --build -d --wait --wait-timeout 180`.
- [ ] Run `docker compose -p lol-dev exec -T web php scripts/seed-development.php`.
- [ ] Open <http://localhost:8080> and log in as `dill`, `bob`, or `admin` using
  `DEV_PASSWORD` from `.env`, initially `local-player-password`. You can also register.

The seed command creates missing development accounts and useful inventory without
changing existing accounts. It also creates an `admin` password hash in the retained
administrators table; there is no separate administrator UI. Seeding is restricted
to the CLI with `APP_ENV=development`. Database connection settings come from
`DB_HOST`, `DB_NAME`, `DB_USER`, and `DB_PASSWORD`; Compose supplies `DB_HOST=db`.
MySQL has no published host port. The web server binds only to localhost.

Stop with `docker compose -p lol-dev down`. To discard this development database,
run `docker compose -p lol-dev down --volumes`, then repeat startup and seeding.
Use the same project name for these commands. This removes only that project's
volume; do not use Docker prune commands. SQL initialization runs only on an empty
volume. Rebuild the web image after code changes.

## Verification

Run the acceptance command from this repository:

```sh
bash tests/verify.sh
```

The runner creates a unique Compose project and fresh database volume, supplies
its own credentials, and publishes no ports. It checks PHP 8.5 and MySQL 8.4,
lints every PHP and PHP-containing include file, and runs HTTP/database assertions
for strict schema initialization, exact bundled seeds, foreign keys, registration,
login failures, session persistence/logout, movement, combat, encounter rewards,
purchases, equipment, potions, sales, and rejected item access. Encounter fixtures
exist only in the disposable database and private session files. Failures print
container logs; cleanup removes only the invocation's project and volumes.
Unavailable Docker is a failure, never a skipped success.

The implementation environment denied access to the Docker socket and had no host
PHP executable. Consequently neither PHP lint nor database-backed smoke tests could
execute here. Host verification must run the acceptance command above before this
migration is considered verified.

## Preserved content and limitations

All four locations, six monsters, and four item templates are retained, including
the original item spelling. Valid starter inventory is recreated by the development
seed command. Invalid zero location references now use Town; optional missing item
references use NULL. Monster gold supports the original 1000-gold rewards. Foreign
keys reference matching indexed columns, including quests referencing item IDs.

No adventure rows, quest rows, or adventure images were bundled. Their tables remain;
when no adventure is available the game falls back to local monsters, and a location
with neither completes without rewards. Monster drop chances were also absent and
default to zero rather than inventing probabilities. Tests supply their own encounters
and drops. Quest gameplay and additional regions remain unfinished as in the original.

Corrections include constructors, timestamp arithmetic, wearable purchase pricing,
exact-funds purchases, owned-item checks, quantity/equipment updates, turn exhaustion,
encounter persistence, and completion rewards. Rest consumes a turn; refreshing Town
while already there does not. The legacy hourly turn regeneration and class/combat
formulas remain. Existing GET action links and the original layout are retained;
this local development setup is not a broader production security redesign.
