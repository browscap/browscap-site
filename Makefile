.PHONY: *

CLEAR_CONFIG_CACHE=rm -f storage/app/vars/*
OPTS=

help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

build: clean docker-build composer-install ## Set up everything

docker-build: ## Build the Docker containers
	docker compose build

composer-install: ## run unit tests
	docker buildx build -f docker/php/Dockerfile --target=composer_vendor_path --output=vendor .

run: build ## Run the environment so you can visit it locally
	docker compose up -d

clean: ## Clean up stuff
	docker compose down --remove-orphans
	docker-compose rm -f
	rm -Rf vendor
