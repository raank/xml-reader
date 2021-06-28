#!/bin/bash

ENV_FILE=".env"

# Grant Permissions
chmod -R 777 storage
chmod 775 bootstrap/app.php
chmod 775 public/index.php

# Install dependencies
/usr/local/bin/composer install

# Copy environment file
if [ ! -f "$ENV_FILE" ]; then
    cp .env.example ${ENV_FILE}
    chmod 777 ${ENV_FILE}

    # generate security key
    /usr/local/bin/php artisan key:generate
    # generate jwt token
    /usr/local/bin/php artisan jwt:generate
    # generate token security
    /usr/local/bin/php artisan token:generate
fi

# generate security token
/usr/local/bin/php artisan security:generate

/usr/local/bin/php artisan migrate