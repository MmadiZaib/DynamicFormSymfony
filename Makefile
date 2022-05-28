.PHONY: help
.DEFAULT_GOAL = help

DOCKER_COMPOSE=@docker-compose
DOCKER_COMPOSE_EXEC=$(DOCKER_COMPOSE) exec
NODE_DOCKER_COMPOSE_EXEC=$(DOCKER_COMPOSE_EXEC) node
PHP_DOCKER_COMPOSE_EXEC=$(DOCKER_COMPOSE_EXEC) php-fpm
COMPOSER=$(PHP_DOCKER_COMPOSE_EXEC) composer
SYMFONY_CONSOLE=$(PHP_DOCKER_COMPOSE_EXEC) bin/console

## —— Docker 🐳  ———————————————————————————————————————————————————————————————
start:	## Lancer les containers docker
	$(DOCKER_COMPOSE) up -d

stop:	## Arréter les containers docker
	$(DOCKER_COMPOSE) stop

rm:	stop ## Supprimer les containers docker
	$(DOCKER_COMPOSE) rm -f

restart: stop start	## redémarrer les containers

ssh-php:	## Connexion au container php
	$(PHP_DOCKER_COMPOSE_EXEC) bash

ssh-node:	## Connexion au container node
	$(NODE_DOCKER_COMPOSE_EXEC) sh

## —— Symfony 🎶 ———————————————————————————————————————————————————————————————
vendor-install:	## Installation des vendors
	$(PHP_DOCKER_COMPOSE_EXEC) composer install

vendor-update:	## Mise à jour des vendors
	$(COMPOSER) update

composer:	## Composer
	$(PHP_DOCKER_COMPOSE_EXEC) composer $(filter-out $@,$(MAKECMDGOALS))

console:	## Composer
	$(SYMFONY_CONSOLE) $(filter-out $@,$(MAKECMDGOALS))

clean-vendor: cc-hard ## Suppression du répertoire vendor puis un réinstall
	$(PHP_DOCKER_COMPOSE_EXEC) rm -Rf vendor
	$(PHP_DOCKER_COMPOSE_EXEC) rm composer.lock
	$(COMPOSER) install

cc:	## Vider le cache
	$(SYMFONY_CONSOLE) c:c

cc-hard: ## Supprimer le répertoire cache
	$(PHP_DOCKER_COMPOSE_EXEC) rm -fR var/cache/*

clean-db: ## Réinitialiser la base de donnée
	$(SYMFONY_CONSOLE) d:d:d --force --connection --if-exists
	$(SYMFONY_CONSOLE) d:d:c
	$(SYMFONY_CONSOLE) d:m:m --no-interaction
	$(SYMFONY_CONSOLE) d:s:u --force
	$(SYMFONY_CONSOLE) d:f:l --no-interaction

load-fixtures: cc ## load fixtures
	$(SYMFONY_CONSOLE) d:f:l -n


## —— Others 🛠️️ ———————————————————————————————————————————————————————————————
help: ## Liste des commandes
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

