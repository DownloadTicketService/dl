#!/bin/bash
cd /var/www/html/include/scripts
php useradmin.php rm admin
php useradmin.php add admin true admin
echo "WARNING: Adding user admin with password admin, please, change that as soon as possible"
