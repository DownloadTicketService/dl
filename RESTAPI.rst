DL REST API
===========

.. contents::

Since version 0.10 DL offers a "RESTful" HTTP API that allows inter-operation
between other services and/or native programs easily.


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

"POST" requests are ``x-www-form-urlencoded`` and must also include:

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

Since: 0.10

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
    * lastul (integer, since 0.18): maximal number of seconds after last upload for grants.
    * maxul (integer, since 0.18): maximal number of uploads for grants.

  * ticket:

    * total (integer): maximal number of seconds for tickets.
    * lastdl (integer): maximal number of seconds after last download for tickets.
    * maxdl (integer): maximal number of downloads for tickets.

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

Since: 0.10

Request method: "POST"

Request parameters: None

POST "msg" object parameters:

* comment (string): comment for the ticket.
* pass (string): password required for the ticket.
* pass_send (boolean, since: 0.18): send password in email notifications
  (defaults to true when unset)
* ticket_total (integer): maximal number of seconds.
* ticket_lastdl (integer): maximal number of seconds after last download.
* ticket_maxdl (integer): maximal number of downloads for the ticket.
* notify (string): notification addresses (comma-separated list of e-mails).
* send_to (string): send-link-to addresses (comma-separated list of e-mails).
* permanent (boolean): Same as ticket_expiry=never.
* ticket_expiry (choice: auto/once/never/custom, since: 0.18):

  :auto: use server's defaults for ticket expiration
  :once: same as ticket_maxdl=1 with server's default ticket_total
  :never: same as ticket_total/ticket_lastdl/ticket_maxdl=0
  :custom: requires explicit ticket_total/ticket_lastdl/ticket_maxdl

POST "file" parameter:

* File to be attached (mandatory).

Returned values:

* id (string): ticket ID.
* url (string): ticket URL.


newgrant
~~~~~~~~

A "newgrant" request creates a new grant.

Since: 0.13

Request method: "POST"

Request parameters: None

POST "msg" object parameters:

* notify (string): notification address (mandatory).
* comment (string): comment for the grant/ticket.
* pass (string): password required for the grant/ticket.
* pass_send (boolean, since: 0.18): send password in email notifications
  (defaults to true when unset)
* grant_total (integer): maximal number of seconds.
* grant_lastul (integer, since: 0.18): maximal number of seconds after the last
  upload has been triggered.
* grant_maxul (integer, since: 0.18): maximal number of uploads for the grant.
* grant_expiry (choice: auto/once/never/custom, since: 0.18):

  :auto: use server's defaults for grant expiration
  :once: same as grant_maxul=1 with server's default grant_total
  :never: same as grant_total/grant_lastul/grant_maxul=0
  :custom: requires explicit grant_total/grant_lastul/grant_maxul

* ticket_total (integer): maximal number of seconds.
* ticket_lastdl (integer): maximal number of seconds after last download.
* ticket_maxdl (integer): maximal number of downloads for the ticket.
* send_to (string): send-link-to addresses (comma-separated list of e-mails).
* ticket_permanent (boolean): Same as ticket_expiry=never.
* ticket_expiry (choice: auto/once/never/custom, since: 0.18):

  :auto: use server's defaults for ticket expiration
  :once: same as ticket_maxdl=1 with server's default ticket_total
  :never: same as ticket_total/ticket_lastdl/ticket_maxdl=0
  :custom: requires explicit ticket_total/ticket_lastdl/ticket_maxdl

Returned values:

* id (string): grant ID.
* url (string): grant URL.


purgeticket
~~~~~~~~~~~

A "purgeticket" request deletes a ticket ID and its associated file, notifying
the owner (if requested).

Since: 0.11

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


``curl`` examples
-----------------

curl_ can be used to easily fiddle with the REST API.

A minimal request to create a new ticket can be performed with the following
command::

  curl https://dl.example.com/rest.php/newticket \
    -F file=@filename -F msg={} \
    -H 'Authorization: Basic dGVzdDp0ZXN0' \
    -H 'X-Authorization: Basic dGVzdDp0ZXN0'

``@filename`` is a special curl syntax that specifies the path to the
filename to be posted. The basic authorization data is provided manually, as
it needs to be replicated in the non-standard header "X-Authorization" anyway
(this is used as a secondary token to prevent CSRF). You can construct the
authorization hash on the command-line as well with the following::

  echo -n 'user:password' | base64

Please keep in mind command-line arguments are usually visible to other users
running on the same system, potentially exposing your password.

.. _curl: https://curl.haxx.se/


Programming APIs
----------------

Python
~~~~~~

A Python API, supporting both asynchronous/synchronous operations and progress
support can be found in the ``client/dl-wx/dl.py`` file. The API is used both
by ``dl-wx.py`` and ``dl-cli.py`` in the same directory.

A simpler stand-alone implementation which can be helpful for testing can be
found at ``client/dl-cli.py``.
