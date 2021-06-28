#!/bin/bash

GREEN="\033[1;32m"
BLUE="\033[1;34m"
CLEAR="\033[0m"
SWAGGER="swagger.yaml"
SQLITE="database/database.sqlite"

echo "---------------------------------------------"
echo "- ${BLUE}MAKING SQLITE${CLEAR}"
echo "---------------------------------------------"
if [ -f "$SQLITE" ]; then
    rm ${SQLITE}
fi
touch ${SQLITE}
chmod 777 ${SQLITE}
php artisan migrate --database=testing

echo "\n---------------------------------------------"
echo "- ${BLUE}PHP UNIT${CLEAR}"
echo "---------------------------------------------"
./vendor/bin/phpunit

echo "\n---------------------------------------------"
echo "- ${BLUE}PHP STAN${CLEAR}"
echo "---------------------------------------------"
./vendor/bin/phpstan analyse -c phpstan.neon --memory-limit=-1

echo "---------------------------------------------"
echo "- ${BLUE}PHP MD${CLEAR}"
echo "---------------------------------------------"
./vendor/bin/phpmd app,tests ansi cleancode,codesize,controversial,design,naming,unusedcode

echo "\n---------------------------------------------"
echo "- ${BLUE}DOC API${CLEAR}"
echo "---------------------------------------------"
./vendor/bin/openapi --bootstrap ./routes/api.php --output ./${SWAGGER} ./routes ./app
echo "Swagger file path: ${GREEN}$(dirname $(readlink -f "$SWAGGER"))/${SWAGGER}${CLEAR}\n"