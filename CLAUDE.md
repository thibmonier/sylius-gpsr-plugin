# CLAUDE.md

Sylius 2 plugin displaying the product safety information required by Art. 19 of the
General Product Safety Regulation (EU) 2023/988. Functional spec and legal mapping: `SPEC.md`.
Public docs: `README.md`.

## Commands

PHP needs more than 128 MB to compile the test application container: prefix console and
PHPStan calls with `php -d memory_limit=2G` when running locally.

```bash
docker compose up -d mysql                # MySQL on 3306
vendor/bin/phpunit --testsuite unit
vendor/bin/phpunit --testsuite non-unit   # functional tests, need the test DB + Sylius fixtures
vendor/bin/phpstan analyse                # level max, src + tests
vendor/bin/ecs check
vendor/bin/console lint:container
vendor/bin/console sylius:fixtures:load tmonier_gpsr -n   # demo GPSR data (after the default suite)
```

Local env overrides (gitignored): `tests/TestApplication/.env.local` (MAILER_DSN) and
`.env.test.local` (DATABASE_URL without password for the Docker MySQL).

## Architecture

- `src/Entity` — `Manufacturer`, `ResponsiblePerson` (share `AddressableTrait`),
  `ProductSafety` + `ProductSafetyTranslation` (Sylius translatable pattern). XML mapping in
  `config/doctrine` (mapped superclasses); validation as attributes, group `sylius`.
- `ProductSafety` owns the one-to-one with the product: never add a field to the Product entity.
- `src/Form/EventSubscriber/ProductSafetyFormSubscriber` + `Form/Extension/ProductTypeExtension`
  — unmapped `productSafety` sub-form on the admin product form (the form is a live component:
  keep the logic in form events, the controller flushes).
- `src/Resolver/ProductSafetyResolver` → `Model/ProductSafetyView`, Twig function
  `tmonier_gpsr_product_safety`. Reads translations from the collection directly
  (`getTranslation()` would create empty ones).
- `src/Grid/Filter/GpsrDataMissingFilter` — custom grid filter added to `sylius_admin_product`.
- Admin CRUD uses `type: sylius.resource` routes (`config/routes/admin.yaml`), grids in
  `config/grids/gpsr.yaml`, hook prefixes `tmonier_gpsr.admin.manufacturer` /
  `tmonier_gpsr.admin.responsible_person`.
- Model class parameters `%tmonier_gpsr.model.*.class%` are set in `prepend()` because grids
  are processed before the extension loads.

## Rules

- EU membership is `Model/EuMemberStates` (EU-27 only, EEA not included — documented choice).
- Hook context values may be missing when null: use `hookable_metadata.context.x|default(null)`.
- Migrations use the Schema API (MySQL + PostgreSQL), and contain plugin tables only.
- Every translation key must exist in en, fr, de, es, it, nl, pl (`TranslationsTest`). Clear
  the cache (dev and test) after adding a translation file.
- Sylius pluralises `responsible_person` as `responsible_people`: keep that translation key.
