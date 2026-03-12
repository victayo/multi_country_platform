# Multi Country Platform

## Installation

This project runs two Laravel microservices (`hr_service` and `hub_service`) with Docker:

- `hr_app` (PHP-FPM)
- `hub_app` (PHP-FPM)
- `nginx`
- `postgres`
- `redis`
- `rabbitmq`

### Prerequisites

- Docker Desktop (or Docker Engine + Docker Compose v2)
- Hosts entries:
  - `127.0.0.1 hr.localhost`
  - `127.0.0.1 hub.localhost`

### Steps

1. From the project root, create the external Docker network (first time only):

	```bash
	docker network create mcp_network
	```

2. Ensure env files exist for both services:

	- `hr_service/.env`
	- `hub_service/.env`

3. In both env files, set database host/port to Docker PostgreSQL:

	```dotenv
	DB_CONNECTION=pgsql
	DB_HOST=postgres
	DB_PORT=5432
	DB_USERNAME=mcp_user
	DB_PASSWORD=mcp_password
	```

4. Set service-specific database names:

	- In `hr_service/.env`: `DB_DATABASE=hr_service`
	- In `hub_service/.env`: `DB_DATABASE=hub_service`

5. Build and start containers:

	```bash
	docker compose build hr_app hub_app
	docker compose up -d
	```

6. Generate app keys (one time per service):

	```bash
	docker compose exec hr_app php artisan key:generate
	docker compose exec hub_app php artisan key:generate
	```

7. Clear config cache and run migrations in both services:

	```bash
	docker compose exec hr_app php artisan config:clear
	docker compose exec hub_app php artisan config:clear
	docker compose exec hr_app php artisan migrate --force
	docker compose exec hub_app php artisan migrate --force
	```

8. Access the apps:

	- `http://hr.localhost:8080`
	- `http://hub.localhost:8080`
