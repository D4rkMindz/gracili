#!/bin/bash
# Count all currently running workers
# can be cross checked by using following command
# $ ps -fe | grep enqueue | grep -v grep

# https://dirask.com/posts/Bash-get-current-script-directory-path-1X9E8D
DIRECTORY=$(cd "$(dirname "${BASH_SOURCE[0]}")" &> /dev/null && pwd)
ENVIRONMENT=$(php "$DIRECTORY"/../environment.php)
# in dev, this might not count as expected (sub processes)
COUNT=$((`ps -ef | grep "[e]nqueue.php --$ENVIRONMENT" | grep -v grep -c`))
echo "$COUNT"