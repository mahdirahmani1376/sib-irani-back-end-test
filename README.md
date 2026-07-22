## Setup

- clone the repo and run the following commands

```sh
git clone https://github.com/mahdirahmani1376/sib-irani-back-end-test.git
cd sib-irani-back-end-test
cp ./src/.env.example ./src/.env
cp ./src/.env.testing.example ./src/.env.testing
docker compose up -d --build
docker exec -it sb-php composer install
docker exec -it sb-php php artisan migrate --force --seed
docker exec -it sb-php php artisan key:generate --env testing
docker exec -it sb-php php artisan test
```