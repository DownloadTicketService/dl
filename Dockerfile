FROM php:7.0-apache

USER root

RUN mkdir /app
RUN mkdir -p /var/spool/dl/data
ADD docker/data.sdb /app/
ADD docker/change_admin_pass.sh /usr/local/bin/
ADD docker/add_admin_user.sh /usr/local/bin/

RUN chmod +x /usr/local/bin/change_admin_pass.sh
RUN chmod +x /usr/local/bin/add_admin_user.sh

RUN mkdir -p /var/log/dl
RUN chown www-data:www-data /var/spool/dl /var/log/dl -R

LABEL version="0.17.1"
LABEL description="DL-Ticket by Yuri D’Elia <wavexx@thregr.org>"
LABEL mantainer "Roberto Salgado <drober@gmail.com>"

VOLUME /var/spool/dl

EXPOSE 80

ENV SQL_URI sqlite:\$spoolDir/data.sdb

ADD htdocs/ /var/www/html/
# ADD https://www.thregr.org/~wavexx/software/dl/releases/dl-0.17.1.zip /var/www/html/


#RUN chmod 0770 /var/spool/dl/data.sdb
COPY docker/config.php.dist /var/www/html/include/config.php
COPY docker/test.php /var/www/html/
COPY docker/run.sh /app/run.sh

CMD /app/run.sh
