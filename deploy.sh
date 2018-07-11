#!/bin/bash

REMOTE_DIR="/var/www/plzenskybarcamp.cz/www"

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
SSH="ssh mlh"

echo "Uploading data to SSH…"
rsync -rcP --delete --exclude-from="${DIR}/.rsync-exclude" "${DIR}/" "mlh:$REMOTE_DIR/"

echo "Replace file permissions…"
$SSH sudo fixwww $REMOTE_DIR

echo "Remove temporary files…"
$SSH find $REMOTE_DIR/temp/cache -mindepth 2 -type f -delete

echo -n "Remove nette email-sent marker… "
ssh mlh /bin/bash << EOF
	if [ -f ${REMOTE_DIR}/log/email-sent ]
	then
		rm ${REMOTE_DIR}/log/email-sent
		echo "removed"
	else
		echo "no exists"
	fi
EOF
