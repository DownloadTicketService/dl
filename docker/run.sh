#!/bin/bash

chown www-data\: /var/spool/dl
chmod o= /var/spool/dl

if [ ! -f "/var/spool/dl/data.sdb" ];
then
	echo "Database not found, copying a basic one"
	cp /app/data.sdb /var/spool/dl/
	chown www-data:nogroup /var/spool/dl/data.sdb
	add_admin_user.sh
	echo "
 ___ _   _ _____ ___  
|_ _| \ | |  ___/ _ \ 
 | ||  \| | |_ | | | |
 | || |\  |  _|| |_| |
|___|_| \_|_|   \___/ 

This image contains a tool to change admin password.
To change admin password try:
 docker exec $HOSTNAME change_admin_password.sh YOUR_NEW_PASSWORD
"
	#change_admin_pass.sh admin
fi

if [ ! -d "/var/spool/dl/data" ];
then
	echo "Data spool directory not found, creating"
	mkdir -m 0770 /var/spool/dl/data
	chown www-data /var/spool/dl/data
fi



apache2-foreground
