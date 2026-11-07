#!/bin/bash

docker build -t laravel-wizard-php-cli .

echo ""
echo "exit with ctrl+d"
docker run --rm -it --user www-data -v "$(pwd):/var/www/html" --entrypoint bash laravel-wizard-php-cli
