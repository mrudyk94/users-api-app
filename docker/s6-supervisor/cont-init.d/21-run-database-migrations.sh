#!/usr/bin/env bash

RUN_MIGRATIONS=${RUN_MIGRATIONS:-false}

if [ "$RUN_MIGRATIONS" != "true" ]
then
  exit 0
fi

# wait for database 15 seconds in any case
wait-for-it.sh -t 15 "$DB_HOST":"$DB_PORT" -- echo "Database is available"

if [ $? -ne 0 ]
then
  echo "Something wrong with database connection to host ${DB_HOST}:${DB_PORT}"
  exit 1
fi

# try to run migrations
s6-setuidgid www-data php "$APP_SOURCE_FOLDERPATH"/bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

if [ $? -ne 0 ]
then
  echo "Something wrong with migrations..."
  exit 1
fi
