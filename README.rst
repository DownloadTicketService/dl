===========================
dl: download ticket service
===========================

dl is a file exchange service that allows you to upload any file to a web
server and generate a unique ticket for others to download. The ticket is
automatically expired according to the specified rules, so that you don't need
to keep track or cleanup afterward. dl also allows you to grant an anonymous,
one-time upload for others to send *you* a file, without the requirement of
account management.

dl is usually installed as a "email attachments replacement" due to its
simplicity (though can be used in other ways).

.. contents::


Requirements
============

* PHP 5.3 or higher.
* PHP SQLite module (or another PDO database module).
* PHP mbstring module.
* PHP OpenSSL module.
* Web server access for installation and setup.


Installation
============

The installation is divided into three steps:

1) Installation of the files
2) Database creation/setup or database upgrade
3) Initial user creation

Due to the number of possible configurations, the installation must be carried
on manually.


DL installation
---------------

* Copy the ``htdocs`` directory contained in the archive to a directory of
  choice under your web server.

* Copy ``include/config.php.dist`` to either ``include/config.php`` or
  ``/etc/dl.php`` and customize as needed. This usually means changing the URL
  and E-Mail of the service.

* Ensure the ``include`` and ``style/include`` directories are *not* directly
  accessible externally. Type ``http://dl.example.com/include/config.php`` and
  verify that you *correctly* get an "Access Denied" error.

  If you use Apache, the provided ``.htaccess`` files should be already
  sufficient; consult your web server documentation otherwise.

* Create a spool directory that will be used by the service to store the files,
  user and ticket information. This directory *should* be outside of the
  document root of your web server. Fix the permissions so that PHP has
  read/write access to it.

  In the provided ``include/config.php`` this is configured as
  ``/var/spool/dl``. If you web server runs as "nobody:nogroup", issue::

    mkdir -p -m770 /var/spool/dl
    chgrp nogroup /var/spool/dl

  to create correctly this directory.

* Create a database (as described in `Database setup`_).

* Create at least one user (as described in `Internal authentication`_).


Database setup
--------------

DL needs a database to store the ticket and user information. By default, DL
will use an embedded "sqlite" database stored within the spool directory, but
some manual setup is still required.

To create the database, you need the ``sqlite3`` command.
On Ubuntu/Debian, ``sqlite3`` can by installed by executing::

  sudo apt-get install sqlite3

You should then execute the provided SQL batch for SQLite *with the same user
as your web server* (by using ``su`` or ``sudo`` if necessary)::

  cd /var/spool/dl/
  sqlite3 data.sdb < /your-installation-directory/include/scripts/db/sqlite.sql
  chmod 660 data.sdb

If you want to change the database path, or use a real database server, you
need to properly configure the ''$dsn'' parameters in ``include/config.php``
according to your setup. The DSN string changes according to the PDO module
that you want to use. Please see one of:

* `SQLite DSN <http://www.php.net/manual/en/ref.pdo-sqlite.connection.php>`_
* `MySQL DSN <http://php.net/manual/en/ref.pdo-mysql.connection.php>`_
* `PostgreSQL DSN <http://www.php.net/manual/en/ref.pdo-pgsql.connection.php>`_

for the most popular configuration choices. When a username/password is
required, using the appropriate variables ''$dbUser/$dbPassword'' is preferred
instead of embedding the values in the DSN string.

The directory ``include/scripts/db/`` provides SQL initialization scripts for
SQLite, MySQL and PostgreSQL.


Upgrading from DL <= 0.3
~~~~~~~~~~~~~~~~~~~~~~~~

Upgrading from DL 0.3 or earlier is possible without loosing any data by using
the bundled ``include/scripts/dbupgrade.php`` script. Proceed as follows:

* Backup your current ``config.php`` file and spool directory.

* Overwrite the DL installation directory with the new copy.

* Customize the new ``config.php`` file.

* Create a new database as described above in `Database setup`_.

* If you had to change the ''$dbHandler'' parameter in DL 0.3,
  copy the same setting into the new configuration file.

* Run ``dbupgrade.php`` as your web server user::

    cd /your-installation-directory/include/scripts
    php dbupgrade.php

* Test your new setup.

* You must now remove ''$dbHandler'' from the new configuration file and
  ``data.db``, ``user.db`` from the spool directory.


Upgrading from DL 0.3 and any later version
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

* Backup your current ``config.php`` file and spool directory.

* Overwrite the DL installation directory with the new copy.

* Either copy over the old ``config.php`` file or customize the new version.

* Run ``dbupgrade.php`` as your web server user::

    cd /your-installation-directory/include/scripts
    php dbupgrade.php

* Test your new setup.


PHP setup
---------

The following parameters are required to be set in your ``php.ini`` (these
values are defaults since PHP 5.0, but they might be different in your setup):

* ``magic_quotes_gpc``: must be "Off".
* ``magic_quotes_runtime``: must be "Off".
* ``date.timezone``: must be set to your system preference.

The maximal upload limit is determined by several PHP configuration
parameters. Check your ``php.ini`` for:

* ``file_uploads``: must be "On".
* ``upload_tmp_dir``: ensure enough space is available.
* ``upload_max_filesize``: change as needed.
* ``post_max_size``: must be at least 1M larger than upload_max_filesize.
* ``session.gc_maxlifetime``: must be long enough to allow large uploads to finish.

The upload limit as shown in the submission form is determined automatically
from the ``upload_max_filesize`` parameter.

Any upload beyond ``post_max_size`` will be completely ignored: users will get
a blank page instead of an error message. You should raise ``post_max_size``
above ``upload_max_filesize`` to get an acceptable "error window".

You should also check ``session.gc_maxlifetime`` (in seconds) to be long enough
for your users to complete a large upload. Uploading 500MB on a slow ADSL
connection can take as much as 12 hours, so set it to *at least* 43200.

If you are allowed to do so, you can also set these parameters with "ini_set()"
directly inside ``include/config.php`` (so that it only affects dl). If PHP was
built as an Apache module you can also set them through ``.htaccess`` (see
http://www.php.net/manual/en/configuration.changes.php).


User setup
----------

DL can use both an internal and an external user database, by trusting the
authentication credentials provided by your web server.

dl supports both "normal" users and "administrators". A normal user can only
see and manage tickets created by himself. Administrators can see/manage all
the tickets.


Internal authentication
~~~~~~~~~~~~~~~~~~~~~~~

Once dl has been installed and PHP is set-up correctly, you have to create at
least one user to be able to log in. User management is handled through the
command line by using the bundled ``useradmin.php`` utility.

On the server, execute the following commands *with the same user as your web
server* (by using ``su`` or ``sudo`` if necessary)::

  cd /your-installation-directory/include/scripts
  php useradmin.php add "admin" "true" "change me"

where:

* ``admin`` is the user name
* ``true`` (or ``false``) sets the administrator status
* ``change me`` is the password

Repeat as many times as necessary. You should now be able to use the web
service. Other users/administrators can be added through the web interface.


External authentication
~~~~~~~~~~~~~~~~~~~~~~~

External authentication should be the preferred form of authentication for
corporate use since it supports whatever authentication scheme your web server
already supports (for example, LDAP, ActiveDirectory, etc).

To enable external authentication you have to protect the two files:

* ``admin.php``
* ``rest.php``

using a "Basic" authentication scheme. You should then set ''$authRealm'' to
the same authentication realm used in your web server. The other files *must
not* be protected.

DL will implicitly trust the credentials provided by the web server. All users
are logged in as "normal" by default. The only setup required is adding the
administrators with ``useradmin.php`` without using any password.

Logout with HTTP authentication is not guaranteed to work: users should simply
**close their browser** to clear their session (closing a tab or window is not
enough in many browsers). Currently, logout works as expected on:

* Firefox
* Safari
* Google Chrome/Chromium

Logout does not work on:

* Internet Explorer 7/8.
* Opera 9/10.

Again, only the *Basic* authentication is supported, which transmits the
password in clear-text unless you use SSL.

When using external authentication, the HTTP header ``USER_EMAIL`` can
additionally provide the user's default email address. Such header is provided
automatically, for example, when using "LemonLDAP::NG".


Large file support
------------------

Large file support (for uploads larger than 2GB) requires a combination of PHP
version, web server and browser support.

Uploads of files larger than 2GB is only supported with PHP 5.6 and beyond.

Apache 2.2 and above support large request bodies but needs to be built for
64bit (see ``LimitRequestBody``). Same for Lighttpd 1.4 (>2gb but only for
64bit builds, see ``server.max-request-size``).

Finally, not all browsers support large file uploads:

============= ============
Browser       Upload limit
============= ============
IE <= 8       2gb
IE >= 9       no limit
Firefox <= 16 2gb
Firefox >= 17 no limit
Chrome        no limit
Opera >= 10   no limit
============= ============

Sources:

* http://www.motobit.com/help/scptutl/pa98.htm
* https://bugzilla.mozilla.org/show_bug.cgi?id=215450
* http://blogs.msdn.com/b/ieinternals/archive/2011/03/10/wininet-internet-explorer-file-download-and-upload-maximum-size-limits.aspx


Web-server Configuration
========================

Apache/mod_php
--------------

With internal authentication::

  <Directory /your-installation-directory>
    AcceptPathInfo On
    AllowOverride Limit
    Options -Indexes
    DirectoryIndex index.php index.html
  </Directory>

With external authentication::

  <Directory /your-installation-directory>
    AcceptPathInfo On
    AllowOverride Limit
    Options -Indexes
    DirectoryIndex index.php index.html
    <FilesMatch "^(admin|rest)\.php$">
      AuthType Basic
      AuthName "Restricted Area"
      ...
      Require valid-user
    </FilesMatch>
  </Directory>

With LDAP or ActiveDirectory authentication::

  <Directory /your-installation-directory>
    AcceptPathInfo On
    AllowOverride Limit
    Options -Indexes
    DirectoryIndex index.php index.html
    <FilesMatch "^(admin|rest)\.php$">
      AuthType Basic
      AuthName "Restricted Area"
      AuthBasicProvider ldap
      AuthzLDAPAuthoritative off
      AuthLDAPURL ldap://XXXXXX:XXXX/ou=XXXX,dc=XXXX,dc=XXX?sAMAccountName?sub?(objectClass=*)
      AuthLDAPBindDN "cn=XXXX,ou=XXXXX,dc=XXX,dc=XXX"
      AuthLDAPBindPassword "XXXXX"
      ...
      Require valid-user
      Satisfy any
    </FilesMatch>
  </Directory>


Apache/FastCGI
--------------

FastCGI support in Apache up to 2.2.x is severely lacking with all the
available modules: ``mod_fcgi``, ``mod_fcgid`` (now merged officially into
Apache's ``mod_fcgi``) and ``mod_fastcgi``.

* ``mod_fcgi`` and ``mod_fcgid`` buffer the entire request in memory before
  handing-off the request to PHP, meaning that the maximal upload limit is
  bound to your available memory at the time of the request, independently of
  how PHP is setup. This is a known, old bug_ that's still present in both
  ``mod_fcgi`` 2.2.14 and ``mod_fcgid`` 2.3.4. There is no known work-around:
  either use ``mod_php`` or use a different server.

* ``mod_fastcgi`` has been proved to be slow (and sometimes unstable) in most
  configurations. It is not advisable to use PHP with ``mod_fastcgi``.

.. _bug: http://sourceforge.net/mailarchive/forum.php?thread_name=48485BDC.1020204@oxeva.fr&forum_name=mod-fcgid-users

For the REST service to work, independently of the authentication method,
``mod_rewrite`` needs to be enabled and configured as follows::

  <Directory /your-installation-directory>
    AcceptPathInfo On
    AllowOverride Limit
    Options -Indexes
    DirectoryIndex index.php index.html
    <FilesMatch "^(admin|rest)\.php$">
      RewriteEngine on
      RewriteCond %{HTTP:Authorization} ^(.*)
      RewriteRule ^(.*) - [E=HTTP_AUTHORIZATION:%1]
    </FilesMatch>
  </Directory>

This is required to correctly pass the ``Authorization`` header to the PHP
process.

If you want to enable HTTP/External authentication, just add the usual
authorization configuration as well::

  <Directory /your-installation-directory>
    AcceptPathInfo On
    AllowOverride Limit
    Options -Indexes
    DirectoryIndex index.php index.html
    <FilesMatch "^(admin|rest)\.php$">
      RewriteEngine on
      RewriteCond %{HTTP:Authorization} ^(.*)
      RewriteRule ^(.*) - [E=HTTP_AUTHORIZATION:%1]
      AuthType Basic
      AuthName "Restricted Area"
      ...
      Require valid-user
    </FilesMatch>
  </Directory>


Lighttpd/FastCGI
----------------

PHP/FastCGI works fine with Lighttpd 1.4.x without any special setup. The
following configuration is required to protect the include directories::

  $HTTP["url"] =~ "^/dl(?:/|/.*/)include/" {
    url.access-deny = ( "" )
  }

You can also enable external authentication with the following::

  $HTTP["url"]    =~ "^/dl/(?:admin|rest)\.php$" {
    auth.require  += ( "" => (
	"method"  => "basic",
	"realm"   => "Restricted Area",
	"require" => "valid-user"
    ) )
  }


Nginx/FastCGI
-------------

Nginx in combination with PHP/FastCGI works fine but needs special configuration to
setup ``PATH_INFO`` correctly. Here is an example configuration with DL
installed as a subdirectory in the document root::

  location ^~ /dl {
      # Set maximum upload size. Should be the same as PHP's upload_max_filesize
      client_max_body_size 512M;

      # Protect the include directories
      location ~ ^/dl(?:/|/.*/)include {
	  deny all;
      }
      try_files $uri $uri/ @dlcleanurl;

      # Enable PHP
      location ~ \.php$ {
	  try_files $uri =404;
	  include php_fastcgi;
      }
  }

  # DL 'clean url'
  location @dlcleanurl {
      include php_fastcgi;
      fastcgi_split_path_info       ^(.+\.php)(/.*)$;
      fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
      fastcgi_param PATH_INFO       $fastcgi_path_info;
  }


Ticket expiration
=================

Ticket expiration can be either performed internally to DL (the default), or by
using the external ``include/scripts/expire.php`` utility with a cron job. This
preference can be set by controlling the ''$gcInternal'' parameter.

The internal method requires no setup, but the external method has the added
advantage of not interrupting the web interface during the expiration process,
and also ensures that the spool is emptied when DL itself is not used actively.


Internal method
---------------

Expiration is usually performed aggressively at every page request. You can
control this behavior (thus reducing the DB pressure) by tuning the
''$gcProbability'' and ''$gcLimit'' parameters.

If you notice too much load on your DB, start by lowering ''$gcProbability'' to
0.5 and set ''$gcLimit'' to roughly the number of active tickets currently
present in your DB.

Continue to lower ''$gcProbability'' even further until the load becomes
acceptable. When the load is acceptable, but queries take too long, reduce
''$gcLimit''.


External method
---------------

Simply call ``include/scripts/expire.php`` within a cron job, which should be
executed with *the same user as the web server*. Executing the script once a
day is sufficient for low traffic websites, but can be executed as often as
needed. ''$gcLimit'' still controls how many tickets are expired for each run
to limit the execution time.


Known limitations/Browser support
=================================

* Tested with Safari 3.x/4.x, Firefox 3.x/4.x, Explorer 7/8,
  Google Chrome/Chromium.
* Tested with PHP 5.x.


Internationalization support
============================

DL is fully translated in English, German, French, Spanish and Italian. DL will
attempt to detect the correct locale of the browser and use it if available,
otherwise switch to the customizable default. The user can however switch the
language anytime.

Adding a new translation is easy for anyone familiar with the `gettext` tools:
you don't need programming knowledge. If you want to contribute a new language,
we recommend to subscribe to the mailing list and ask for guidance. We really
appreciate your help.


Contributing a new translation
------------------------------

Contributing a new translation is easy enough:

* Edit ``include/lang.php`` and add your new language name and alias to
  ``$langData``, as done for the other languages.

* Execute::

    mkdir -p include/locale/lang/LC_MESSAGES/
    cd include/scripts/
    ./langgen.php > ../locale/lang/LC_MESSAGES/messages.po

  where *lang* is the full locale name you just added.

* Translate the freshly generated ``messages.po`` using a text editor, or
  by using PoEdit_, or any other "po" editing tool.

* To test/update the translations run ``langupd.php``::

    cd include/scripts/
    ./langupd.php

* Optionally translate the user guide, which is located in
  ``include/static/guide/``.

  Copy the english directory tree into a new tree with the new locale name and
  translate ``index.txt``. ``index.html`` is regenerated automatically with
  docutils_ by running ``langupd.php`` as before.

Updating an existing translation
--------------------------------

Updating an existing translation is very similar to contributing a new
translation. Again, we can make use of the `gettext` tools to refresh the
existing files:

* Execute::

    cd include/scripts/
    ./langgen.php > ../locale/lang/LC_MESSAGES/messages.po.ref

  to generate the full list of strings to be translated.

* Merge the existing translations, by executing::

    cd ../locale/lang/LC_MESSAGES/
    msgmerge -U messages.po messages.po.ref

* Examine the updated ``messages.po`` file using any "PO" editing tool or text
  editing, looking for new strings to be translated, strings which have been
  removed, and fuzzy matches which might require tweaks in the translation.

* To test/update the translations run ``langupd.php``::

    cd include/scripts/
    ./langupd.php

.. _PoEdit: http://poedit.sourceforge.net/
.. _docutils: http://docutils.sourceforge.net/


Usage
=====

DL should be usable by users without any training. The web interface must be
self-explanatory. If you find the usage to be difficult or that the interface
could be improved, **it's a bug**. Please let us know.


Command-line client: ``dl-cli``
-------------------------------

A command-line client to the REST interface is included in the distribution in
``client/dl-cli.py``. This client requires a simple text configuration file, by
default stored in ``~/.dl.rc``, containing the following values:

* url: REST URL of the service
* user: your user name
* pass: your password
* verify (optional): "true" or "false": enable/disable SSL verification
  (might be required for testing, but defaults to true)

An example::

  url=https://dl.example.com/rest.php
  user=test
  pass=test

Simply run the command with no arguments to see usage information. At least
Python 2.7 is required, with the "PycURL" module installed. Under Debian/Ubuntu
systems you can install the required dependencies by doing the following::

  sudo apt-get install python-pycurl


Graphical client: ``dl-wx``
---------------------------

A graphical client is also included in the distribution, which allows to create
tickets easily from the system's taskbar. The client can be run by executing
``client/dl-wx/dl-wx.py`` or by downloading an `executable client`_.

Upon first execution the user will be prompted for the basic configuration.
After that all DL functions can be operated through the taskbar icon:

* Left-clicking on the taskbar will create a new ticket using the default
  settings.
* Right-clicking allows to select different actions.
* On OSX, you can drop files directly on the dock.

At least Python 2.7 is required, with the "ConfigObj", "PycURL" and "wxPython"
modules installed. Under Debian/Ubuntu systems you can install the required
dependencies by doing the following::

  sudo apt-get install python-pycurl python-configobj python-wxgtk2.8

``dl-cli`` and ``dl-wx`` share the same configuration file, so both can be used
interchangeably.

A ``dl-wx`` pre-built binary is also available online on the dl-wx_ page, which
includes installation instructions and a simple tutorial.

.. _executable client:
.. _dl-wx: http://www.thregr.org/~wavexx/software/dl/dl-wx.html


Thunderbird integration
-----------------------

The bundled extension "Thunderbird-Filelink-DL" integrates with the new
Thunderbird's Filelink_ functionality, by using the REST service provided by DL
0.10 and onward. The extension allows to convert large attachments to links
automatically, directly within the Composer window.

To install the extension, go to Thunderbird's "Tools" .. "Addons" menu, and
click on the "Settings" icon just next to the search bar. Select "Install
Add-on from file..." and choose the file ``client/thunderbird-filelink-dl.xpi``
as provided in the distribution (or directly on the extension_ web page).

Once installed, just follow the official Filelink_ usage tutorial as provided
by Mozilla for configuration and usage.

.. _Filelink: https://support.mozillamessaging.com/en-US/kb/filelink-large-attachments
.. _extension: http://www.thregr.org/~wavexx/software/dl/thunderbird.html


General/support mailing list
============================

If you feel to discuss improvements, customizations and suggestions, you can
subscribe to `dl-ticket-service` by either sending an empty email to
<dl-ticket-service+subscribe@thregr.org>, using GMane_ (group
"gmane.comp.web.dl-ticket-service.general") or by contacting the author at
<wavexx@thregr.org>. The archives are accessible via web through
http://news.gmane.org/gmane.comp.web.dl-ticket-service.general or via news
directly.

.. _GMane: http://www.gname.org/


Customisation and development
=============================

You are encouraged to change DL as you see fit under the terms of the GNU GPL
2 license. DL's GIT repository is publicly accessible at::

  git://src.thregr.org/dl


Authors and Copyright
=====================

"dl" can be found at http://www.thregr.org/~wavexx/software/dl/

"dl" is distributed under GNU GPL 2, WITHOUT ANY WARRANTY.
Copyright(c) 2007-2012 by Yuri D'Elia <wavexx@thregr.org>.
