test:
	@sh tests.sh
    
doc:
    @./vendor/bin/openapi --bootstrap ./routes/api.php --output ./swagger.yaml -f .yaml ./routes/api.php ./api/**
    @echo "Documentation Generated on \".swagger.yaml\""

install:
	@docker-compose up -d --build
	@docker exec -it raank-php sh configure.sh

status:
	@docker ps -f name=raank

up:
	@docker-compose up -d
