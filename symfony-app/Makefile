
is_docker := $(shell if [ -f /.dockerenv ]; then echo "yes"; fi)
 
DOCKER_CMD = docker-compose exec symfony
 
ifdef is_docker
    ifeq ($(is_docker), yes)
        DOCKER_CMD =
    endif
endif
 
clear_cache:
	$(DOCKER_CMD) php bin/console cache:clear
	$(DOCKER_CMD) php bin/console cache:pool:prune
	$(DOCKER_CMD) php bin/console cache:warmup
 
	$(DOCKER_CMD) php bin/console cache:pool:clear cache.global_clearer
	$(DOCKER_CMD) php bin/console cache:pool:clear cache.system_clearer
	$(DOCKER_CMD) php bin/console cache:pool:clear cache.app_clearer
 
	$(DOCKER_CMD) php bin/console doctrine:cache:clear-metadata
	$(DOCKER_CMD) php bin/console doctrine:cache:clear-query
	$(DOCKER_CMD) php bin/console doctrine:cache:clear-result
 
generate_migrations:
	$(DOCKER_CMD) php bin/console doctrine:migrations:diff
 
run_migrations:
	$(DOCKER_CMD) php bin/console doctrine:migrations:migrate --no-interaction
 
run_dry_fixer:
	$(DOCKER_CMD) php vendor/bin/php-cs-fixer fix --dry-run --verbose --show-progress=dots
 
run_fixer:
	$(DOCKER_CMD) php vendor/bin/php-cs-fixer fix --verbose --show-progress=dots