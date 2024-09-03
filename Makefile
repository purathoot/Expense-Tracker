.PHONY: build up migrate down setup copy-env

# Build Docker images
build:
	docker-compose build

# Start Docker containers
up: build copy-env
	docker-compose up -d

# Run database migrations
migrate:
	docker-compose exec expense_tracker php bin/console doctrine:migrations:migrate --no-interaction

# Stop and remove Docker containers
down:
	docker-compose down

# Setup the project
setup: up migrate
	echo "Setup complete. Access your application at:"
	echo "Backend: http://localhost:8000"

# Copy .env.example to .env
copy-env:
	if [ ! -f .env ]; then \
		cp .env.example .env; \
		echo ".env file created from .env.example"; \
	else \
		echo ".env file already exists"; \
	fi
