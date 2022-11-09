## help : Available commands list
help: Makefile
	@sed -n 's/^##//p' $<
## up   : Containers up
up:
	docker compose -f=docker/docker-compose.yml down
	docker compose -f=docker/docker-compose.yml up -d
	docker exec -it psr-http-stream-php sh
## down : Containers down
down:
	docker compose -f=docker/docker-compose.yml down