#!/bin/sh
PUID=${PUID}
PGID=${PGID}

addgroup -g "$PGID" -S abc
adduser -u "$PUID" -S abc -G abc

echo '
-------------------------------------
GID/UID
-------------------------------------'
echo "
User uid:    $(id -u abc)
User gid:    $(id -g abc)
-------------------------------------
"

# Make sure files/folders needed by the processes are accessable when they run under the abc user
chown -R abc:abc /config
chown -R abc:abc /var/www
chown -R abc:abc /run
chown -R abc:abc /var/lib/nginx
chown -R abc:abc /var/log/nginx

chmod o+w /dev/stderr
chmod o+w /dev/stdout
su-exec abc:abc /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf

