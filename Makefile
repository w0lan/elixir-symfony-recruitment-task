GREEN := \033[0;32m
YELLOW := \033[0;33m
NC := \033[0m

.PHONY: help
help: ## Show available commands
	@echo "${GREEN}Dostępne komendy:${NC}"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  ${YELLOW}%-16s${NC} %s\n", $$1, $$2}'

.PHONY: up
up: ## Start containers (build + up)
	docker compose up -d --build

.PHONY: down
down: ## Stop containers
	docker compose down

.PHONY: clean
clean: ## Stop containers and remove volumes
	docker compose down -v

.PHONY: logs
logs: ## Show logs (follow)
	docker compose logs -f

.PHONY: build
build: ## Build images
	docker compose build

.PHONY: shell-phoenix
shell-phoenix: ## Open a shell in the Phoenix container
	docker compose exec phoenix /bin/sh

.PHONY: shell-symfony
shell-symfony: ## Open a shell in the Symfony container
	docker compose exec symfony /bin/sh

.PHONY: shell-db
shell-db: ## Open psql in the DB container
	docker compose exec db psql -U postgres -d phoenix_app

.PHONY: symfony-composer
symfony-composer: ## Run composer in Symfony container (ARGS="...")
	docker compose run --rm --no-deps symfony composer $(ARGS)

.PHONY: symfony-install
symfony-install: ## Install Symfony deps (composer install)
	docker compose run --rm --no-deps symfony composer install

.PHONY: symfony-console
symfony-console: ## Run bin/console in Symfony container (ARGS="...")
	docker compose run --rm --no-deps symfony php bin/console $(ARGS)

.PHONY: test
test: test-phoenix test-symfony ## Run all tests

.PHONY: test-phoenix
test-phoenix: ## Run Phoenix tests
	docker compose exec phoenix sh -c "MIX_ENV=test mix test"

.PHONY: test-symfony
test-symfony: ## Run Symfony tests
	docker compose run --rm --no-deps symfony php bin/phpunit

.PHONY: lint
lint: lint-cs lint-phpstan ## Run all linters

.PHONY: lint-cs
lint-cs: ## Check code style (PHP CS Fixer)
	docker compose run --rm --no-deps symfony composer exec php-cs-fixer fix -- --dry-run --diff

.PHONY: fix-cs
fix-cs: ## Fix code style (PHP CS Fixer)
	docker compose run --rm --no-deps symfony composer exec php-cs-fixer fix

.PHONY: lint-phpstan
lint-phpstan: ## Run static analysis (PHPStan)
	docker compose run --rm --no-deps symfony composer exec phpstan -- analyse --memory-limit=1G
