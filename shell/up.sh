#!/bin/bash
MYSQL_ENV_FILE="mysql-variables.env"
SYMFONY_ENV_FILE="symfony-variables.env"
if [ ! -f "$MYSQL_ENV_FILE" ] || [ ! -f "$SYMFONY_ENV_FILE" ]; then
  #vars
  read -p "Enter APP_ENV value: " APP_ENV
  ROOT_PASSWORD=`< /dev/urandom tr -dc _A-Z-a-z-0-9 | head -c${1:-32};echo;`
  PASSWORD=`< /dev/urandom tr -dc _A-Z-a-z-0-9 | head -c${1:-32};echo;`
  SYMFONY_APP_SECRET=`< /dev/urandom tr -dc _A-Z-a-z-0-9 | head -c${1:-32};echo;`
  USER_DATABASE=`basename $PWD`
  DB_CONTAINER="db"
  #mysql file
  echo "MYSQL_ROOT_PASSWORD=$ROOT_PASSWORD" > "$MYSQL_ENV_FILE"
  echo "MYSQL_DATABASE=$USER_DATABASE" >> "$MYSQL_ENV_FILE"
  echo "MYSQL_USER=$USER_DATABASE" >> "$MYSQL_ENV_FILE"
  echo "MYSQL_PASSWORD=$PASSWORD" >> "$MYSQL_ENV_FILE"
  #symfony file
  echo "APP_ENV=$APP_ENV" > "$SYMFONY_ENV_FILE"
  echo "APP_SECRET=$SYMFONY_APP_SECRET" >> "$SYMFONY_ENV_FILE"
  echo "DATABASE_URL=mysql://$USER_DATABASE:$PASSWORD@$DB_CONTAINER:3306/$USER_DATABASE?serverVersion=8.0" >> "$SYMFONY_ENV_FILE"
  echo 'CORS_ALLOW_ORIGIN=^https?://(localhost|127\.0\.0\.1)(:[0-9]+)?$' >> "$SYMFONY_ENV_FILE"
fi
docker-compose up -d
if [ $? -ne 0 ]; then
  echo "Docker compose failed"
  exit 1
fi

docker exec -it `basename $PWD`_php_1 bash shell/up.sh
