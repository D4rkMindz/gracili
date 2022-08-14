#!/bin/bash
LIMIT=4
# Start services
# Run this as a cron
# https://geekflare.com/auto-restart-services-when-down/
# also count the processes that are running

## https://dirask.com/posts/Bash-get-current-script-directory-path-1X9E8D
DIRECTORY=$(cd "$(dirname "${BASH_SOURCE[0]}")" &> /dev/null && pwd)

ENVIRONMENT=$(php "$DIRECTORY"/../environment.php)
COUNT=$(sh "$DIRECTORY"/count.sh)

if [ "$COUNT" -lt $LIMIT ]
then
  echo "Starting enqueue ($ENVIRONMENT)"
  nohup php "$DIRECTORY"/enqueue.php --"$ENVIRONMENT" > /dev/null &
  sleep 1 # this sleep is required to decouple the process to be able to count the processes below accurately

  # on dev, the output of count is doubled (on the server, the running process count is correct)!
  echo "Currently running: $(sh "$DIRECTORY"/count.sh) / $LIMIT"
else
  echo "Already running enough queues in $ENVIRONMENT ($COUNT / $LIMIT)"
fi