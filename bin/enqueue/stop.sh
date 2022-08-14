#!/bin/bash
# Stop all currently running workers

# https://dirask.com/posts/Bash-get-current-script-directory-path-1X9E8D
DIRECTORY=$(cd "$(dirname "${BASH_SOURCE[0]}")" &> /dev/null && pwd)

ENVIRONMENT=$(php "$DIRECTORY"/../environment.php)
COUNT=$(sh "$DIRECTORY"/count.sh)

if [ "$COUNT" -gt 0 ]
then
  echo "Killing $COUNT enqueue processes in $ENVIRONMENT"
  kill $(ps aux | grep "[e]nqueue.php --$ENVIRONMENT" | awk '{print $2}')
else
  echo "No enqueue processes to kill"
fi