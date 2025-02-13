#!/bin/bash

set -e
set -x

if [ ! -f .env ]
then
    cp .env.dist .env
fi

if [[ "$(uname -sr)" == *"Darwin"* ]]
then
    cp docker-compose.dist.yaml docker-compose.override.yaml
fi

docker compose up -d --build
docker compose exec php composer install
chmod 0777 var/log

docker compose exec php composer recreate-db
