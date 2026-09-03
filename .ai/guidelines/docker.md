# Docker

This application runs in Docker Compose. Never run PHP tooling on the host.

- Prefix every PHP command with `docker compose exec php`, e.g. `docker compose exec php php artisan test --compact`, `docker compose exec php vendor/bin/pint --dirty --format agent`, `docker compose exec php composer show --direct`.
- This applies to `artisan`, `pint`, `pest`/`phpunit`, `composer`, and `tinker`. The project's PHP version and extensions live in the image, not on the host.
- The repository is bind-mounted at `/var/www/html`, so files edited on the host are visible to the container immediately.
- Use the running `php` service. The `testing` service is a CI-only Compose profile with no bind mount — do not use it for local runs.
