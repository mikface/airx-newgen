chmod +x bin/console
if [ "$APP_ENV" = "prod" ]; then
  exit 0;
fi

settingUpDb() {
  if [ ! -f ".db-created" ]; then
    bin/console doctrine:schema:create --quiet 2>/dev/null && touch .db-created
  else
    bin/console doctrine:schema:update --force --quiet 2>/dev/null
  fi
}

echo "SETTING UP THE DB..."
until settingUpDb;
do
  settingUpDb
  sleep 5
done
echo "OK"
