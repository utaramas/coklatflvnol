#!/bin/bash
#
# This script configures WordPress file permissions based on recommendations
# from http://codex.wordpress.org/Hardening_WordPress#File_permissions
#
# Author: Michael Conigliaro (https://gist.github.com/macbleser/9136424)
#
WP_ROOT=${1:-.} # <-- wordpress root directory, current directory by default
[ -e "$WP_ROOT/wp-config.php" ] || { echo "Usage: $0 /path/to/wordpress"; exit; } # <-- detect that the directory is a wordpress root
WP_OWNER=$(id -u $(logname)) # <-- wordpress owner (This assumes the wordpress owner is the logged in user)
WP_GROUP=$(id -g $(logname)) # <-- wordpress group (This assumes the wordpress owner is the logged in user)
WS_GROUP=$(
     source /etc/apache2/envvars 2>/dev/null && # This works on debian-based systems at least
     echo "$APACHE_RUN_GROUP" ||
     echo nobody  
) # <-- webserver group
echo "Fixing permissions on $WP_ROOT"
echo "Wordpress owner.group: $WP_OWNER.$WP_GROUP"
echo "Web Server group: $WS_GROUP"

echo 'reset to safe defaults'
find ${WP_ROOT} -exec chown ${WP_OWNER}:${WP_GROUP} {} \;
find ${WP_ROOT} -type d -exec chmod 755 {} \;
find ${WP_ROOT} -type f -exec chmod 644 {} \;

echo 'allow wordpress to manage wp-config.php (but prevent world access)'
chgrp ${WS_GROUP} ${WP_ROOT}/wp-config.php
chmod 660 ${WP_ROOT}/wp-config.php

echo 'allow wordpress to manage .htaccess'
touch ${WP_ROOT}/.htaccess
chgrp ${WS_GROUP} ${WP_ROOT}/.htaccess
chmod 664 ${WP_ROOT}/.htaccess

echo 'allow wordpress to manage wp-content'
find ${WP_ROOT}/wp-content -exec chgrp ${WS_GROUP} {} \;
find ${WP_ROOT}/wp-content -type d -exec chmod 775 {} \;
find ${WP_ROOT}/wp-content -type f -exec chmod 664 {} \;