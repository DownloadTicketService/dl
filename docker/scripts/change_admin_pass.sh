#!/bin/bash

if [ -z "$1" ];
then
	echo "Must provide password to set"
	exit 1
fi

cd /var/www/html/include/scripts/
php useradmin.php passwd "admin" "$1"
