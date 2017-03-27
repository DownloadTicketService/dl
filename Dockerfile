FROM php:7.0-apache

LABEL version="0.17.1"
LABEL description="DL-Ticket by Yuri D’Elia <wavexx@thregr.org>"
LABEL mantainer "Roberto Salgado <drober@gmail.com>"


USER root

# Create app directory for entrypoint and templates
RUN mkdir /app ; mkdir /app/config

# Copy templates folder
ADD docker/templates /app/templates

# Copy scripts folder
ADD docker/scripts /app/scripts

# Include a DL Config inside "include" folder to load config from "/app/config" so we can use a volume for it
COPY docker/replacements/config.inc.php /var/www/html/include/config.php

# Adding user managemet tools
## change_admin_pass will set admin password with ease
## add_admin_user will create user admin in case that the DB was missing and an empty db template was copied.
# Create log folder, just in case
RUN ln -s /app/scripts/change_admin_pass.sh /usr/local/bin/change_admin_pass && \
    ln -s /app/scripts/add_admin_user.sh /usr/local/bin/add_admin_user && \
    chmod -R +x /app/scripts/*.sh && \
    mkdir -p /var/log/dl && \
    chown -R www-data:www-data /var/log/dl

# Use a volume for the default storing path
VOLUME /var/spool/dl
VOLUME /app/config

# Container exposing port 80
## if you want to use HTTPS, try Traefik, HAProxy or Apache Proxy
EXPOSE 80

# Default SQLite URI
ENV SQL_URI sqlite:\$spoolDir/data.sdb

# Default PHP uploads limitation
ENV UPLOAD_MAX_FILESIZE 20M
ENV POST_MAX_SIZE 20M

# Default PHP memory limit
ENV MEMORY_LIMIT 25M


# Copying DL public content into apache's exposed folder
ADD htdocs/ /var/www/html/
# ADD https://www.thregr.org/~wavexx/software/dl/releases/dl-0.17.1.zip /var/www/html/

# Use run script as container's CMD
CMD /app/run.sh
