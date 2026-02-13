#!/usr/bin/with-contenv bash

echo 'Checking startup commands...'

STARTUP_COMMANDS="$(env | grep "STARTUP_COMMAND_*")"

eval "s6-setuidgid www-data composer run-script post-install-cmd --no-interaction"

if [ -n "$STARTUP_COMMANDS"  ];
then
    env | grep "STARTUP_COMMAND_*" | sort | while IFS='=' read -r name value; do
        echo "Running '$name': '$value'..."
        eval "s6-setuidgid www-data $value"
        echo "done."
    done
else
    echo "There is no any startup command to execute."
fi
