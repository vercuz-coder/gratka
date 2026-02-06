.DEFAULT_GOAL := help

is_docker := $(shell if [ -f /.dockerenv ]; then echo "yes"; fi)
 
DOCKER_CMD = docker-compose exec symfony
 
ifdef is_docker
    ifeq ($(is_docker), yes)
        DOCKER_CMD =
    endif
endif

help: ## Show this help message
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'
 
## Cache

clear_cache: ## Clear and warmup cache
	$(DOCKER_CMD) php bin/console cache:clear
	$(DOCKER_CMD) php bin/console cache:pool:prune
	$(DOCKER_CMD) php bin/console cache:warmup
 
	$(DOCKER_CMD) php bin/console cache:pool:clear cache.global_clearer
	$(DOCKER_CMD) php bin/console cache:pool:clear cache.system_clearer
	$(DOCKER_CMD) php bin/console cache:pool:clear cache.app_clearer
 
	$(DOCKER_CMD) php bin/console doctrine:cache:clear-metadata
	$(DOCKER_CMD) php bin/console doctrine:cache:clear-query
	$(DOCKER_CMD) php bin/console doctrine:cache:clear-result
 
## Migrations

generate_migrations: ## Generate migrations by comparing database with entities
	$(DOCKER_CMD) php bin/console doctrine:migrations:diff
 
run_migrations: ## Run all pending migrations
	$(DOCKER_CMD) php bin/console doctrine:migrations:migrate --no-interaction

## PHP CS Fixer
 
run_dry_fixer: ## Run PHP CS Fixer in dry-run mode
	$(DOCKER_CMD) php vendor/bin/php-cs-fixer fix --dry-run --verbose --show-progress=dots
 
run_fixer: ## Run PHP CS Fixer and fix files
	$(DOCKER_CMD) php vendor/bin/php-cs-fixer fix --verbose --show-progress=dots

## PHPStan

run_phpstan: ## Run PHPStan analysis
	$(DOCKER_CMD) php vendor/bin/phpstan analyse --memory-limit=512M

run_phpstan_baseline: ## Generate PHPStan baseline
	$(DOCKER_CMD) php vendor/bin/phpstan analyse --memory-limit=512M --generate-baseline

## Testing

test: ## Run all tests
	$(DOCKER_CMD) php vendor/bin/phpunit

test_unit: ## Run unit tests
	$(DOCKER_CMD) php vendor/bin/phpunit --testsuite=Unit

test_integration: ## Run integration tests
	$(DOCKER_CMD) php vendor/bin/phpunit --testsuite=Integration

test_functional: ## Run functional tests
	$(DOCKER_CMD) php vendor/bin/phpunit --testsuite=Functional

test_coverage_text: ## Run tests and show coverage in text format
	$(DOCKER_CMD) php vendor/bin/phpunit --coverage-text

test_coverage_html: ## Run tests and generate coverage report in HTML
	$(DOCKER_CMD) php vendor/bin/phpunit --coverage-html var/coverage