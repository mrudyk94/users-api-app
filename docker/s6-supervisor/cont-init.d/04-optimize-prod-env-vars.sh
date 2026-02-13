#!/usr/bin/with-contenv bash

echo 'Optimize prod env vars...'

# if this is prod mode so generate an optimized .env.local.php which overrides all other configuration files
if [ "$APP_ENV" = "prod" ]
then
  s6-setuidgid www-data composer --working-dir="$APP_SOURCE_FOLDERPATH" dump-env prod
fi

