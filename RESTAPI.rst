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

Request parameters: None

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

Example request:

.. code:: http

  GET /rest/info HTTP/1.0
  Host: dl.example.com
  Authorization: Basic dGVzdDp0ZXN0
  X-Authorization: Basic dGVzdDp0ZXN0

Example answer:

.. code:: http

  HTTP/1.1 200 OK
  Content-Type: application/json

.. code:: json

  {
    "version": "0.11",
    "url": "http:\/\/www.thregr.org\/~wavexx\/software\/dl\/",
    "masterpath": "http:\/\/dl.example.com\/",
    "maxsize": 209715200,
    "defaults":
    {
      "grant":
      {
	"total": 31536000
      },
      "ticket":
      {
	"total": 31536000,
	"lastdl": 2592000,
	"maxdl": 0
      }
    }
  }


newticket
~~~~~~~~~

A "newticket" request creates a new ticket.

Request method: "POST"

Request parameters: None

POST "msg" object parameters:

  * comment (string): comment for the ticket.
  * pass (string): password required for the ticket.
  * ticket_total (integer): maximal number of seconds.
  * ticket_lastdl (integer): maximal number of seconds after last download.
  * ticket_maxdl (integer): maximal number of downloads for the ticket.
  * notify (string): notification addresses (comma-separated list of e-mails).
  * send_to (string): send-link-to addresses (comma-separated list of e-mails).
  * permanent (boolean): mutually exclusive with hra/dn/dln, sets a permanent
    ticket.

POST "file" parameter:

  * File to be attached (mandatory).

Returned values:

  * id (string): ticket ID.
  * url (string): ticket URL.


purgeticket
~~~~~~~~~~~

A "purgeticket" request deletes a ticket ID and its associated file, notifying
the owner (if requested).

Request method: "POST"

Request parameters:

  * ticket-id: mandatory

POST "msg" object parameters: None

Returned values: None

Example request:

.. code:: http

  POST /rest/purgeticket/c1e3c2e0b6d5d0f0ada292c081fc4c49 HTTP/1.0
  Host: dl.example.com
  Authorization: Basic dGVzdDp0ZXN0
  X-Authorization: Basic dGVzdDp0ZXN0
  Content-Type: application/x-www-form-urlencoded

  msg={}

Example answer:

.. code:: http

  HTTP/1.1 200 OK
  Content-Type: application/json

  {}
