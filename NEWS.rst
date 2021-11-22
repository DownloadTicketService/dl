dl 0.19: 2021-??-??
-------------------

* Fixed various compatibility issues with PHP 8 (includes an update of
  the built-in PHP-Gettext to 1.0.12).
* Raised minimum required PHP version to 7.0.
* Enforce display_errors=Off once logging is setup.
* Fix temporary ZIP file creation (thanks to @SQ-SEN).
* Fix spurious notices in basefuncs.php (thanks to Emanuele Rosati).
* Allow unicode filenames in ``dl-cli`` (thanks to @mjg)


dl 0.18.1: 2017-09-06
---------------------

* Fix upgrade script and incompatibilities when using MySQL.
  Thanks to Daniel Berteaud.


dl 0.18: 2017-09-04
-------------------

Major new features:

* Support for multiple file uploads in both tickets and grants. When multiple
  files are attached, a Zip archive is automatically created with the contents.
  The PHP "Zip" extension is now required.
* Grants are now reusable. With the new defaults, senders are no longer
  restricted to a single use/file per grant, but can keep reusing the same link
  as needed. The grant, just like a ticket, is then automatically expired when
  left unused for a certain amount of time.
* Tickets generated while using a grant are now split into a separated
  "Received files" page. The "All tickets" page reserved to administrators
  still shows all tickets combined and color-coded.
* A new Android client is now available: ``PokéDL``.

Enhancements:

* The ticket and grant expiration parameters have been streamlined for common
  usage patterns, becoming mostly self-explanatory.
* When using a grant, the user can now attach a comment alongside the uploaded
  file/s. The comment is sent back to the grant owner in the notification.
* Tickets now show the generating grant ID in the edit/detailed view.
* The grant comment assigned during creation is now shown in both the tooltip
  of the grant list and in email notifications involving grant usage.
* Ticket/grant passwords were previously always included in notifications. The
  password sending policy can now be controlled at creation time, and defaults
  to sending the password only when automatically generated.
* The subject prefix in email notifications can now be customized.
* ``dl-wx`` now allows to generate grants.
* General ``dl-cli`` overhaul:

  - dl-cli now runs under both python 3 and python 2.7, preferring python 3
  - The password can be read from an external command using ``passcmd``
  - Public-key pinning is now supported through the ``fingerprint`` option
  - Multiple files can now be uploaded in a single ticket (for efficiency,
    dl-cli generates a Zip archive locally before uploading)
  - When generating a grant, the email address is now optional if available
    in the configuration file
  - The ConfigObj module is now required

Bug fixes:

* Tickets generated while using a grant were incorrectly calculating the expiry
  from the grant *creation* time, resulting in premature expiration. Ticket
  expiry is now calculated starting at actual *upload* time.
* Download of files larger than 2GB would previously fail when using DL with
  MySQL or Postgres. Fix by Daniel Berteaud.
* Ticket and grant invalid access or invalid password attempts are now logged.
* Left-clicking on the ``dl-wx`` tray's icon on Linux now works as expected.

Other changes:

* The minimum required PHP version has been raised to 5.5 or higher.
* Important PHP settings are now preset in the bundled ``htdocs/.htaccess``
  file for the Apache/mod_php combination.
* Simplified Chinese translation by Guangyu Dong.
* Russian translation by Олейник О.В.
* The Thunderbird add-on has been updated to support Thunderbird 52.
* The Windows ``dl-wx`` executable has been rebuilt with SNI support.

Please note: DL 0.18 requires a database schema update! Please read the
database upgrade procedure in the README!


dl 0.17.1: 2016-05-01
---------------------

* Filenames are now sanitized more aggressively when received. This avoids
  browser/client failures when receiving files that contain illegal characters
  for the current platform (which might be legal in another).
* Filenames containing multibyte characters could previously result in
  unexpected truncation; they're now handled correctly.
* The uploaded filename is included in grant notifications.
* The Thunderbird add-on has been updated to support Thunderbird 45.
* In the ticket details, the full timestamp of the download is now shown.
* Minor code and documentation fixes.


dl 0.17: 2015-06-26
-------------------

* Login attempts are now logged.
* Log messages are now more uniform, always including the remote address and
  username (when available).
* The Thunderbird add-on has been updated to support Thunderbird 38.


dl 0.16: 2015-01-22
-------------------

* The database connection is automatically re-established when timed out after
  slow uploads/downloads (affects systems not using sqlite).
* dl-cli can now prompt for a password when left unspecified in the
  ``~/.dl.rc`` configuration file.
* Added Japanese translation by Teruo IWAI.
* Dutch user-guide translation by Maarten Schoonman.


dl 0.15: 2014-11-28
-------------------

* File names with special and/or UTF-8 characters are now correctly preserved
  on all browsers.
* Added Dutch translation by Maarten Schoonman.


dl 0.14: 2014-10-16
-------------------

* Fixed broken ``Content-Length`` header with the Apache/mod_php/mod_deflate
  combination, which would prevent downloads to be resumed.
* The built-in skin has been updated.
* The skin can now be customized and set in the configuration file.
* A work-around has been found to allow PHP 5.4-5.5 to upload files up to 4GB
  (note that starting with PHP 5.6 there is no upload size limitation).
* Logging of server-side errors has been improved.


dl 0.13: 2014-07-31
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


dl 0.12: 2013-12-10
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


dl 0.11: 2013-07-05
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


dl 0.10.1: 2012-03-09
---------------------

* A bug was fixed in the initialization code that could cause grant uploads to
  fail in certain configurations.


dl 0.10: 2012-02-06
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


dl 0.9.1: 2011-12-31
--------------------

* Fixed a grave security issue: unauthorized parties can perform login as any
  arbitrary user when using the built-in authentication mechanism by supplying
  an authorization header. DL versions down to 0.3 are affected.


dl 0.9: 2011-04-06
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


dl 0.8: 2010-07-10
------------------

* Update PHP-Gettext to 1.0.10 (fixing several PHP Notices).
* Fix browser language autodetection (typo, thanks to Bert-Jan Kamp).
* Fix ticket expiration when using sqlite3 (table locking issues).
* Do not purge tickets prematurely after an unsuccessful download.
* Purge tickets immediately after the download, when possible.
* "useradmin.php" now allows to reset/change user role and password.
* All notifications are now sent using the default locale.
* Ticket expiration can be performed with an external utility.
* The user-guide is now included in the admin interface.


dl 0.7: 2010-03-10
------------------

* Fix XSS vulnerability for unknown ticket IDs (discovered by Sven Eric Neuz)


dl 0.6: 2010-03-03
------------------

* Remember the selected language with a cookie.
* Allow to tune the DB expiration process to improve the performance.
* Fixed E-Mail subject encoding.
* German translation update.
* PHP 5.3 warning fixes.


dl 0.5: 2010-02-09
------------------

* Fix upload progress-bar on Chrome and Safari.
* Minor bug, UI and usability fixes.
* Internationalization support.
* Italian and German translation.
* License changed to GNU GPL 2.


dl 0.4: 2009-11-24
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


dl 0.3: 2009-09-02
------------------

* CSS-ification, with new skin from Kim Thostrup <kim@thostrup.dk>.
* Include IE5/6 PNG fix from Angus Turnbull http://www.twinhelix.com
* Multiuser support with HTTP authentication or internal user database.
* License changed to LGPL 3


dl 0.2: 2007-10-10
------------------

* Renamed "aux.php" to "funcs.php" to avoid "reserved file name" errors
  under Windows.
* Support commas in addition to semicolons as e-mail separators in the
  notify field.
* Removed the 'ID' field in "active tickets" listings.
* Allow to attach a comment in any ticket.
* Byte-ranges support.


dl 0.1: 2007-06-15
------------------

* First release.
