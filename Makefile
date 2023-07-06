build:
	COMPOSE_DOCKER_CLI_BUILD=1 DOCKER_BUILDKIT=1 docker-compose build
run:
	docker-compose up -d

stop:
	docker-compose down
	
install:
	docker-compose exec app php artisan migrate --force && docker-compose exec app php artisan finance:install