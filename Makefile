test:
	@sh tests.sh

install:
	@docker-compose up -d --build
	@docker exec -it raank-php sh configure.sh

status:
	@docker ps -f name=raank

up:
	@docker-compose up -d
