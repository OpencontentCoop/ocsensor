#!/usr/bin/env bash

ID=$@

php extension/ocsensor/bin/php/reindex_by_group.php --allow-root-user -sbackend --groups=${ID} > /dev/null &
