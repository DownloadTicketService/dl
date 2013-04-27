TODO
====

Important
---------

* Multiple-file support vs grant re-use (allow more than one grant, grant
  timeout).

  There are several ideas here:

  - Either allow grants to be used multiple times (lame), or
  - Allow uploading multiple files in both ticket and grants, or
  - Allow to upload many files in the tickets/grants form, but generate
    many tickets in the result (which is the easiest to implement right now).
  - Simply allow grants to work like tickets:

    - assign max/hours since last use/uses for each grant
    - allow the resulting grant to expire like a ticket would
    - user can re-use the grant without limits until it expires

  See [private] discussion with Mokrani for more info.

  Even though generating multiple tickets as a result might be seen as a
  problem, this should result in a single email. Tickets would need to be
  grouped in some way, probably with a new "group" table.

* Server-side preferences: default notification address, default expiry
  settings for both tickets and grants.

* Move alien tickets/grants from the administrator's lists to a separated page.
  My take: definitely important, high priority.

* Use file templates instead of hard-coded messages to compose the e-mails.
  See a lot of good suggestions sent by Morkani.

* Hash-based file references, required to implement de-duplication,
  multiple tickets, e-mail tracking. See:

  http://thread.gmane.org/gmane.comp.web.dl-ticket-service.general/140

* Javascript/validation of e-mail address-es.

* Javascript/validation of numerical fields.

* Set sidewide limits (not just defaults) for ticket/grants in config.php.


Nice to have
------------

* Keep expired ticket IDs into the DB to avoid possible reuse?
  My take: easy to implement, nice to have, but very low priority

* On-line registry of downloaded files/addresses for active tickets/grants
  (right now we only have a log file).

* File picking from a directory (patch available)

  My take: nice to have, important for ftp/rsync upload/ticket conversion, but
  low priority.

  In my opinion there should be a better way to generate a ticket from the
  command line. This should enable DL to be integrated with ftp or other file
  services, scripts, and so on. Issues with permissions of the script.

* Support e-mail tracking when using send-link-to.
  Nice to have, but not critical.

* Some sort of quota mechanism to avoid uncontrolled server fill-up.

* Switch from md5 for password hashing to some salted hash (using crypt()).
  Low priority, since passwords are actually sent in clear-text all over the place.


Suggestions
-----------

* Domain/ip locking of tickets (patch available)
  My take: no strong feeling, low priority.

* Internal IP limit for admin access.
  Easy, but not critical. See:

  http://thread.gmane.org/gmane.comp.web.dl-ticket-service.general/112
