#!/bin/bash

echo "## Create database ##"
bin/console d:d:c
echo "## Schema update ##"
bin/console d:s:u -f

exec "$@"