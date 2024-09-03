.PHONY: build up migrate down setup copy-env

# Setup the project
setup: copy-env up build composer migrate 
	echo "Setup complete. Access your application at:"
	echo "Backend: http://localhost:8080"

# Build Docker images
build:
	docker-compose build

# Start Docker containers
up: build copy-env
	docker-compose up -d

composer:
	composer install
	docker-compose exec expense_tracker php bin/console cache:clear


# Run database migrations
migrate:
	docker-compose exec expense_tracker php bin/console doctrine:migrations:migrate --no-interaction

# Stop and remove Docker containers
down:
	docker-compose down

# Copy .env.example to .env
copy-env:
	if [ ! -f .env ]; then \
		cp .env.example .env; \
		echo ".env file created from .env.example"; \
	else \
		echo ".env file already exists"; \
	fi
