# Multi Country Platform (Laravel)

This project runs Laravel with Docker services for:

- app (PHP-FPM 8.2)
- nginx (HTTP server)
- mysql (database)
- redis (cache)
- rabbitmq (message broker)

## Prerequisites

- Docker Desktop (or Docker Engine + Compose v2)
- Docker Compose (`docker compose` command)

## Project Structure Notes

- Laravel app source is in `html/`
- Docker files are in the repository root
- `docker-compose.yml` mounts `./html` into `/var/www/html` for both `app` and `nginx`

## Installation (Docker)

Run the following from the repository root (the folder that contains `docker-compose.yml`):

1. Create the external Docker network (first time only):

	```bash
	docker network create mcp_network
	```

2. Build the app image:

	```bash
	docker compose build app
	```

3. Start all services:

	```bash
	docker compose up -d
	```

4. Ensure environment file exists in `html/.env` and has Docker DB values:

	```dotenv
	DB_CONNECTION=mysql
	DB_HOST=mysql
	DB_PORT=3306
	DB_DATABASE=multi_country_platform
	DB_USERNAME=mcp_user
	DB_PASSWORD=mcp_password
	```

5. Clear cached config and run migrations:

	```bash
	docker compose exec app php artisan config:clear
	docker compose exec app php artisan migrate
	```

## Access

- App URL: `http://localhost:8080`
- RabbitMQ UI: `http://localhost:15672` (guest / guest)
- MySQL host from your machine: `127.0.0.1:3310`

## Useful Commands

- Show logs:

  ```bash
  docker compose logs -f
  ```

- Open a shell in app container:

  ```bash
  docker compose exec app bash
  ```

- Check migration status:

  ```bash
  docker compose exec app php artisan migrate:status
  ```

- Stop services:

  ```bash
  docker compose down
  ```

## Troubleshooting

### `SQLSTATE[HY000] [2002] No such file or directory`

Cause: Laravel is trying to connect to MySQL using `localhost` from inside the app container.

Fix: In `html/.env`, set `DB_HOST=mysql` and `DB_PORT=3306`, then run:

```bash
docker compose exec app php artisan config:clear
```
