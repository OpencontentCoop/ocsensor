#!/usr/bin/env bash

SITEACCESS=$1
ID=$2

php runcronjobs.php sqliimport_run > /dev/null &
