# İlisan E-Ticaret Makefile

# Docker Compose Configuration
DOCKER_COMPOSE = docker-compose
DOCKER_COMPOSE_FILE = docker-compose.yml

# Service Names
PHP_SERVICE = php
NGINX_SERVICE = nginx
DB_SERVICE = postgres
REDIS_SERVICE = redis
QUEUE_SERVICE = queue

# Colors for output
RED = \033[0;31m
GREEN = \033[0;32m
YELLOW = \033[1;33m
NC = \033[0m # No Color

.PHONY: help up down restart logs shell composer artisan migrate test cs stan fix build clean

## Show help
help:
	@echo ""
	@echo "$(GREEN)İlisan E-Ticaret Makefile Commands$(NC)"
	@echo ""
	@echo "$(YELLOW)Docker Commands:$(NC)"
	@echo "  make up          - Start all containers"
	@echo "  make down        - Stop all containers"
	@echo "  make restart     - Restart all containers"
	@echo "  make build       - Build all containers"
	@echo "  make logs        - Show container logs"
	@echo "  make clean       - Clean containers and volumes"
	@echo ""
	@echo "$(YELLOW)Application Commands:$(NC)"
	@echo "  make shell       - Access PHP container shell"
	@echo "  make composer    - Run composer commands"
	@echo "  make artisan     - Run artisan commands"
	@echo "  make migrate     - Run database migrations"
	@echo "  make seed        - Run database seeders"
	@echo "  make fresh       - Fresh migration with seed"
	@echo ""
	@echo "$(YELLOW)Development Commands:$(NC)"
	@echo "  make test        - Run tests"
	@echo "  make cs          - Check code style"
	@echo "  make cs-fix      - Fix code style"
	@echo "  make stan        - Run static analysis"
	@echo "  make fix         - Fix all code issues"
	@echo ""
	@echo "$(YELLOW)Quick Setup:$(NC)"
	@echo "  make setup       - Initial project setup"
	@echo "  make install     - Install dependencies"
	@echo ""

## Start all containers
up:
	@echo "$(GREEN)Starting containers...$(NC)"
	$(DOCKER_COMPOSE) -f $(DOCKER_COMPOSE_FILE) up -d
	@echo "$(GREEN)Containers started successfully!$(NC)"

## Stop all containers
down:
	@echo "$(YELLOW)Stopping containers...$(NC)"
	$(DOCKER_COMPOSE) -f $(DOCKER_COMPOSE_FILE) down

## Restart all containers
restart: down up

## Build all containers
build:
	@echo "$(GREEN)Building containers...$(NC)"
	$(DOCKER_COMPOSE) -f $(DOCKER_COMPOSE_FILE) build --no-cache

## Show container logs
logs:
	$(DOCKER_COMPOSE) -f $(DOCKER_COMPOSE_FILE) logs -f

## Show specific service logs
logs-php:
	$(DOCKER_COMPOSE) -f $(DOCKER_COMPOSE_FILE) logs -f $(PHP_SERVICE)

logs-nginx:
	$(DOCKER_COMPOSE) -f $(DOCKER_COMPOSE_FILE) logs -f $(NGINX_SERVICE)

logs-db:
	$(DOCKER_COMPOSE) -f $(DOCKER_COMPOSE_FILE) logs -f $(DB_SERVICE)

## Access PHP container shell
shell:
	$(DOCKER_COMPOSE) -f $(DOCKER_COMPOSE_FILE) exec $(PHP_SERVICE) /bin/sh

## Run composer commands
composer:
	$(DOCKER_COMPOSE) -f $(DOCKER_COMPOSE_FILE) exec $(PHP_SERVICE) composer $(filter-out $@,$(MAKECMDGOALS))

## Run artisan commands
artisan:
	$(DOCKER_COMPOSE) -f $(DOCKER_COMPOSE_FILE) exec $(PHP_SERVICE) php artisan $(filter-out $@,$(MAKECMDGOALS))

## Database migrations
migrate:
	@echo "$(GREEN)Running migrations...$(NC)"
	$(DOCKER_COMPOSE) -f $(DOCKER_COMPOSE_FILE) exec $(PHP_SERVICE) php artisan migrate

## Database seeders
seed:
	@echo "$(GREEN)Running seeders...$(NC)"
	$(DOCKER_COMPOSE) -f $(DOCKER_COMPOSE_FILE) exec $(PHP_SERVICE) php artisan db:seed

## Fresh migration with seed
fresh:
	@echo "$(YELLOW)Fresh migration with seed...$(NC)"
	$(DOCKER_COMPOSE) -f $(DOCKER_COMPOSE_FILE) exec $(PHP_SERVICE) php artisan migrate:fresh --seed

## Install dependencies
install:
	@echo "$(GREEN)Installing dependencies...$(NC)"
	$(DOCKER_COMPOSE) -f $(DOCKER_COMPOSE_FILE) exec $(PHP_SERVICE) composer install
	@echo "$(GREEN)Dependencies installed!$(NC)"

## Run tests
test:
	@echo "$(GREEN)Running tests...$(NC)"
	$(DOCKER_COMPOSE) -f $(DOCKER_COMPOSE_FILE) exec $(PHP_SERVICE) php artisan test

## Run Pest tests
pest:
	@echo "$(GREEN)Running Pest tests...$(NC)"
	$(DOCKER_COMPOSE) -f $(DOCKER_COMPOSE_FILE) exec $(PHP_SERVICE) ./vendor/bin/pest

## Check code style
cs:
	@echo "$(GREEN)Checking code style...$(NC)"
	$(DOCKER_COMPOSE) -f $(DOCKER_COMPOSE_FILE) exec $(PHP_SERVICE) ./vendor/bin/pint --test

## Fix code style
cs-fix:
	@echo "$(GREEN)Fixing code style...$(NC)"
	$(DOCKER_COMPOSE) -f $(DOCKER_COMPOSE_FILE) exec $(PHP_SERVICE) ./vendor/bin/pint

## Run static analysis
stan:
	@echo "$(GREEN)Running static analysis...$(NC)"
	$(DOCKER_COMPOSE) -f $(DOCKER_COMPOSE_FILE) exec $(PHP_SERVICE) ./vendor/bin/phpstan analyse

## Fix all code issues
fix: cs-fix stan
	@echo "$(GREEN)All code issues fixed!$(NC)"

## Initial project setup
setup: up install
	@echo "$(GREEN)Generating application key...$(NC)"
	$(DOCKER_COMPOSE) -f $(DOCKER_COMPOSE_FILE) exec $(PHP_SERVICE) php artisan key:generate
	@echo "$(GREEN)Running migrations...$(NC)"
	$(DOCKER_COMPOSE) -f $(DOCKER_COMPOSE_FILE) exec $(PHP_SERVICE) php artisan migrate
	@echo "$(GREEN)Creating storage link...$(NC)"
	$(DOCKER_COMPOSE) -f $(DOCKER_COMPOSE_FILE) exec $(PHP_SERVICE) php artisan storage:link
	@echo "$(GREEN)Project setup completed!$(NC)"

## Clean containers and volumes
clean:
	@echo "$(RED)Cleaning containers and volumes...$(NC)"
	$(DOCKER_COMPOSE) -f $(DOCKER_COMPOSE_FILE) down -v --remove-orphans
	docker system prune -f

## Queue commands
queue-work:
	$(DOCKER_COMPOSE) -f $(DOCKER_COMPOSE_FILE) exec $(PHP_SERVICE) php artisan queue:work

queue-restart:
	$(DOCKER_COMPOSE) -f $(DOCKER_COMPOSE_FILE) exec $(PHP_SERVICE) php artisan queue:restart

## Cache commands
cache-clear:
	$(DOCKER_COMPOSE) -f $(DOCKER_COMPOSE_FILE) exec $(PHP_SERVICE) php artisan cache:clear

config-clear:
	$(DOCKER_COMPOSE) -f $(DOCKER_COMPOSE_FILE) exec $(PHP_SERVICE) php artisan config:clear

route-clear:
	$(DOCKER_COMPOSE) -f $(DOCKER_COMPOSE_FILE) exec $(PHP_SERVICE) php artisan route:clear

view-clear:
	$(DOCKER_COMPOSE) -f $(DOCKER_COMPOSE_FILE) exec $(PHP_SERVICE) php artisan view:clear

clear-all: cache-clear config-clear route-clear view-clear
	@echo "$(GREEN)All caches cleared!$(NC)"

## Production commands
optimize:
	$(DOCKER_COMPOSE) -f $(DOCKER_COMPOSE_FILE) exec $(PHP_SERVICE) php artisan optimize

## Database backup
backup:
	@echo "$(GREEN)Creating database backup...$(NC)"
	docker exec $(shell docker-compose ps -q $(DB_SERVICE)) pg_dump -U postgres ilisan_eticaret > backup_$(shell date +%Y%m%d_%H%M%S).sql
	@echo "$(GREEN)Backup created!$(NC)"

# Allow passing arguments to targets
%:
	@:
