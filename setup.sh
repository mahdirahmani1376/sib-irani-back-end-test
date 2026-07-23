#!/bin/sh

echo "UID=$(id -u)" >> .env
echo "GID=$(id -g)" >> .env

cp ./src/.env.example ./src/.env
cp ./src/.env.testing.example ./src/.env.testing

docker compose up -d --build

docker exec -it sb-php composer install
docker exec -it sb-php php artisan migrate --force --seed
docker exec -it sb-php php artisan key:generate --env=testing
docker exec -it sb-php php artisan test