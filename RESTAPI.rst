DL REST API
===========

Since version 0.10 DL offers a "RESTful" HTTP API that allows inter-operation
between other services and/or native programs easily.

.. contents::


Outline of the API
------------------

Every request is gated through the "/rest.php" page (or simply "/rest",
depending on the configuration), which is directly on the root of the web
service, for example::

  https://dl.example.com/rest.php

Each action is directly appended to the URI space, followed by parameters (if
any)::

  /rest/request[/parameters]

Every request must be performed via the appropriate HTTP method (either "GET"
or "POST"), and must always include HTTP's "Basic" authorization credentials.

The credentials must also be replicated in a second header "X-Authorization"
(which follows the same syntax as a normal "Basic" authorization scheme).

"POST" requests must also include:

* A JSON-encoded "msg" parameter, with arguments defined by the request. This
  parameter should always be present, even when empty.
* When needed, a "file" parameter with the file to be attached.

The output of every request can be:

* An HTTP error code only (400, 401, 500, etc).
* An HTTP error code with a JSON-encoded message with an "error" term::

    {"error":"error description"}

* A successful HTTP status (200), with a JSON-encoded message (even if empty)
  containing the specific request output.


Available requests
------------------

info
~~~~

An "info" request returns the service defaults and statistics.

Request method: "GET"

Returned values:

  * version (string): DL version.
  * masterpath (string): root URL of the service.
  * url (string): configurable reference URL for the service.
  * maxsize (integer): maximum upload size (in bytes).
  * defaults: service defaults:

    * grant:

      * total (integer): maximal number of seconds for grants.

    * ticket:

      * total (integer): maximal number of seconds for tickets.
      * lastdl (integer): maximal number of seconds after last download for tickets.
      * maxdl (integer): maximal number of seconds for the ticket for tickets.


newticket
~~~~~~~~~

A "newticket" request creates a new ticket. A "file" parameter containing the
attached file *must be present* in the request.

Request method: "POST"

Request parameters:

  * comment (string): comment for the ticket.
  * pass (string): password required for the ticket.
  * ticket_total (integer): maximal number of seconds.
  * ticket_lastdl (integer): maximal number of seconds after last download.
  * ticket_maxdl (integer): maximal number of downloads for the ticket.
  * notify (string): notification addresses (comma-separated list of e-mails).
  * send_to (string): send-link-to addresses (comma-separated list of e-mails).
  * permanent (boolean): mutually exclusive with hra/dn/dln, sets a permanent ticket.

Returned values:

  * id (string): ticket ID.
  * url (string): ticket URL.
