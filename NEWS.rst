dl 0.14:
-------------------

* Fixed broken ``Content-Length`` header with the Apache/mod_php/mod_deflate
  combination, allowing downloads to be correctly resumed.
* The built-in skin can now be changed in the configuration file.
* A word-around has been found to allow PHP 5.4-5.5 to upload files up to 4GB
  (note that starting with PHP 5.6 there is no upload size limitation).


dl 0.13: 31/07/2014
-------------------

* The "Active tickets/grants" pages for administrators now show only their own
  tickets, like for normal users. Other tickets are visible in the new "All
  tickets/grants" pages.
* Upload progress information is now implemented client-side using HTML5/JS,
  which is both more responsive and waives any PHP configuration/version
  restrictions.
* Ticket/grant/user listings can now be sorted by clicking on the table header.
* The date/time format can now be customized.
* Spaces in uploaded file names are now correctly preserved.
* The REST interface can now be used with the built-in authentication method
  without additional configuration *also* when using apache/fcgi.
* The REST interface now supports a method to generate grants.
* The Thunderbird add-on now includes a new menu command (under "Tools") and a
  new toolbar icon in the composer window to generate and insert grants in the
  current message.
* Added Brazilian Portuguese and Czech localizations (thanks to Guilherme
  Benkenstein and Jan Štětina).
* Minor bug/cosmetic fixes.

DL 0.12 is the last release offering an upgrade path from DL 0.3. Version 0.13
can only upgrade from 0.4 and above. If you have an old installation, you'll
need to perform a two step upgrade using an earlier release.


dl 0.12: 10/12/2013
-------------------

* Add a new configurable "e-mail" address in the preferences, which is used as
  a default when creating grants and receiving notifications.
* Support for MySQL and PostgreSQL.
* Support for provided e-mail address with external authentication using
  ``USER_EMAIL`` header (useful for LemonLDAP::NG/webSSO).
* Fix REST interface when used in combination with LemonLDAP::NG/webSSO.
* Improved French localization.
* The timezone of the web interface is now customizable.
* Minor bug/cosmetic fixes.

Please note: DL 0.12 requires a database schema update! Please read the
database upgrade procedure in the README!


dl 0.11: 05/07/2013
-------------------

* Fixed CSRF vulnerability of the admin interface (discovered by Dirk Reimers).
* Mitigations against session fixation attacks (discovered by Dirk Reimers).
* Fixed CSRF vulnerability of the REST interface when used in combination with
  HTTP/external authentication.
* Improved client-side validation of the forms (with HTML5/JS where available).
* Password hashing for the user/ticket/grant DB switched to PHPass.
* Progress bar updating improvements.
* Thunderbird integration is now available through the new included extension
  "Thunderbird-Filelink-DL", which converts attachments to links automatically.
* Minor bug/cosmetic fixes.

Please note: DL 0.11 requires a database schema update! Please read the
database upgrade procedure in the README!

Upgrading to DL 0.11 has implication for existing users. The new hashing scheme
limits usernames to 60 characters and passwords to 72 to prevent DoS attacks.
Users having usernames/passwords exceeding these limits won't be able to login
after the upgrade, and can only be managed manually through the command line.

The password hash of existing users is automatically rehashed using the new
scheme upon a successful login (no password change is required).

The optional password of tickets and grants is similarly affected and upgraded
transparently upon successful usage. Tickets/grants having passwords longer
than 72 characters though will require a manual password reset.

To fully prevent CSRF attacks on the REST interface when used in combination
with HTTP authentication the protocol has been broken. Clients (such as the
supplied "dl-wx") require an upgrade, though new clients can still communicate
to an old server.


dl 0.10.1: 09/03/2012
---------------------

* A bug was fixed in the initialization code that could cause grant uploads to
  fail in certain configurations.


dl 0.10: 06/02/2012
-------------------

* The default configuration file has been renamed to "config.php.dist" and must
  now be manually copied/renamed to be used. If a suitable "config.php" is not
  found in the include/ directory, then the configuration is read from
  "/etc/dl.php". This will allow smoother release upgrades in the future.
* Notifications of tickets and grants now use the same locale that was used
  during the creation of the ticket/grant itself.
* A new "Preferences" page has been added, allowing users to change their
  password (currently supported only for internal authentication).
* Changing "hours after last download" while editing a ticket didn't actually
  extend the ticket lifetime. Changing the ticket lifetime now works correctly.
* "hours after last download" has been changed to "days after last download".
* Default ticket/grant expiration settings have been increased significantly.
* A new REST API has been implemented, allowing external applications to use the
  service programmatically.
* Two python clients have been added to the distribution: a command-line python
  client "dl-cli" and a graphical client "dl-wx".
* Minor bug and UI fixes.

Please note: DL 0.10 requires a database schema and webserver configuration
update! Please read the database upgrade procedure in the README and the
relevant notes about web server configuration.


dl 0.9.1: 31/12/2011
--------------------

* Fixed a grave security issue: unauthorized parties can perform login as any
  arbitrary user when using the built-in authentication mechanism by supplying
  an authorization header. DL versions down to 0.3 are affected.


dl 0.9: 06/04/2011
------------------

* The settings of tickets and grants are now stored independently.
* Default ticket/grant settings can be specified in the config file.
* Most ticket and grant options are now moved into an "advanced" panel.
* Grant notifications now include the ticket password in the message.
* Form validation is now also performed in JavaScript.
* Enlarged the width of the interface to 800px.
* Users management is now available through the web interface.
* French, Italian, Spanish and German translation.
* Multi-line comments can be attached to tickets and grants.
* Improved the e-mail notification text.
* Improved ticket and grant listings.
* Tickets can now be edited after being created.
* Minor bug and UI fixes.


dl 0.8: 10/07/2010
------------------

* Update PHP-Gettext to 1.1.10 (fixing several PHP Notices).
* Fix browser language autodetection (typo, thanks to Bert-Jan Kamp).
* Fix ticket expiration when using sqlite3 (table locking issues).
* Do not purge tickets prematurely after an unsuccessful download.
* Purge tickets immediately after the download, when possible.
* "useradmin.php" now allows to reset/change user role and password.
* All notifications are now sent using the default locale.
* Ticket expiration can be performed with an external utility.
* The user-guide is now included in the admin interface.


dl 0.7: 10/03/2010
------------------

* Fix XSS vulnerability for unknown ticket IDs (discovered by Sven Eric Neuz)


dl 0.6: 03/03/2010
------------------

* Remember the selected language with a cookie.
* Allow to tune the DB expiration process to improve the performance.
* Fixed E-Mail subject encoding.
* German translation update.
* PHP 5.3 warning fixes.


dl 0.5: 09/02/2010
------------------

* Fix upload progress-bar on Chrome and Safari.
* Minor bug, UI and usability fixes.
* Internationalization support.
* Italian and German translation.
* License changed to GNU GPL 2.


dl 0.4: 24/11/2009
------------------

* Ticket activity can be logged to syslog or a file.
* The minimal required PHP version is now 5.0.
* PDO is now used for the users/tickets database (defaulting to a sqlite
  database). Upgrading instructions in the README.
* The submission form now allows to automatically send a link of the ticket to
  the specified address/es.
* A ticket can now require a password to be downloaded.
* "Upload grants" can now be created, allowing others to send you a single file
  through DL.
* Progress-bar indicator during uploads.
* Required fields are highlighted when missing.


dl 0.3: 02/09/2009
------------------

* CSS-ification, with new skin from Kim Thostrup <kim@thostrup.dk>.
* Include IE5/6 PNG fix from Angus Turnbull http://www.twinhelix.com
* Multiuser support with HTTP authentication or internal user database.
* License changed to LGPL 3


dl 0.2: 10/10/2007
------------------

* Renamed "aux.php" to "funcs.php" to avoid "reserved file name" errors
  under Windows.
* Support commas in addition to semicolons as e-mail separators in the
  notify field.
* Removed the 'ID' field in "active tickets" listings.
* Allow to attach a comment in any ticket.
* Byte-ranges support.


dl 0.1: 15/06/2007
------------------

* First release.
