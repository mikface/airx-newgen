#!/bin/bash
if [ ! -f "./.env" ]; then
  touch .env
fi

if [ ! -d "./src/Entity" ]; then
  echo 1
  mkdir ./src/Entity
fi

composer install
if [ $?  -ne 0 ]; then
  echo "Composer failed"
  exit 1
fi
if [ ! -f "./config/jwt/private.pem" ] || [ ! -f "./config/jwt/public.pem" ]; then
  echo "Generating JWT keys..."
  JWT_PASSWORD=`< /dev/urandom tr -dc _A-Z-a-z-0-9 | head -c${1:-32};echo;`
  echo "" > ./.env
  echo "JWT_PASS_PHRASE=$JWT_PASSWORD" >> ./.env
  echo "JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem" >> ./.env
  echo "JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem" >> ./.env
  bin/console lexik:jwt:generate-keypair
fi
