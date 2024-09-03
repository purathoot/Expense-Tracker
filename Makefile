.PHONY: build up migrate down

build:
	docker-compose build

up: build
	docker-compose up -d

migrate:
	docker-compose exec expense_tracker php bin/console doctrine:migrations:migrate --no-interaction

down:
	docker-compose down

setup: up migrate
	echo "Setup complete. Access your application at:"
	echo "Backend: http://localhost:8000"
