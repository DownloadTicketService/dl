from __future__ import unicode_literals, print_function, generators, absolute_import

# Replaced pycurl with curl_cffi
from curl_cffi import requests as crequests
from curl_cffi import CurlMime

from http import client as httplib  # Py3 path; guarded import OK if you need

import binascii
import json
import io
import os
from io import BytesIO
from threading import Thread

try:
    from http import client as httplib
except ImportError:
    import httplib


class Service:
    def __init__(self, url=None, username=None, password=None,
                 verify=None, agent=None):
        self.url = url
        self.username = username
        self.password = password
        self.verify = verify
        self.agent = agent


class DLError(Exception):
    def __init__(self, value):
        self.value = value
    def __str__(self):
        return self.value


class TicketParams:
    def __init__(self, permanent=None, total=None,
                 lastdl=None, downloads=None):
        self.permanent = permanent
        self.total = total
        self.lastdl = lastdl
        self.downloads = downloads


class GrantParams:
    def __init__(self, total=None, ticket_params=None):
        self.total = total
        if ticket_params is None:
            ticket_params = TicketParams()
        self.ticket_params = ticket_params


class Request(Thread):
    def __init__(self, dl, request, msg, file, complete_fn, failed_fn, progress_fn=None):
        super(Request, self).__init__()
        self.dl = dl
        self.request = request
        self.msg = msg
        self.file = file
        self.complete_fn = complete_fn
        self.failed_fn = failed_fn
        self.progress_fn = progress_fn
        self.cancelled = False

        # For compatibility with callers that read these
        self.speed_download = 0
        self.speed_upload = 0

    # Progress/cancel: curl_cffi’s public API doesn’t expose a progress callback like PycURL,
    # so we cannot report live bytes or abort mid-flight. We keep the method for API parity.
    def _progress_noop(self, download_t, download_d, upload_t, upload_d):
        if self.progress_fn:
            self.progress_fn(download_t, download_d, self.speed_download,
                             upload_t, upload_d, self.speed_upload)

    def run(self):
        url = self.dl.service.url.rstrip("/") + "/" + self.request.lstrip("/")

        # Same auth + custom header as original
        auth_pair = f"{self.dl.service.username}:{self.dl.service.password}"
        xauth = binascii.b2a_base64(auth_pair.encode("utf8")).decode("ascii").rstrip()

        # Use a list of tuples so the empty 'Expect:' is preserved (disables 100-continue)
        headers = [
            ("Expect", ""),  # matches original pycurl 'Expect:' line
            ("User-Agent", self.dl.service.agent or ""),
            ("X-Authorization", "Basic " + xauth),
            ("Accept", "application/json"),
        ]  # original header set: Expect:, User-agent, X-Authorization. :contentReference[oaicite:1]{index=1}

        # Match original semantics: default to verify=True unless explicitly False
        verify = True if self.dl.service.verify is None else bool(self.dl.service.verify)

        mp = None
        try:
            method = "GET"
            if self.file or self.msg is not None:
                method = "POST"
                mp = CurlMime()
                if self.file:
                    mp.addpart(
                        name="file",
                        local_path=self.file,
                        filename=os.path.basename(self.file),
                    )
                if self.msg is not None:
                    mp.addpart(
                        name="msg",
                        data=json.dumps(self.msg).encode("utf-8"),
                        content_type="application/json",  # be explicit for servers that care
                    )

            # Perform request
            if method == "POST":
                resp = crequests.post(
                    url,
                    headers=headers,
                    auth=(self.dl.service.username, self.dl.service.password),
                    verify=verify,
                    multipart=mp,
                    allow_redirects=True,
                )
            else:
                resp = crequests.get(
                    url,
                    headers=headers,
                    auth=(self.dl.service.username, self.dl.service.password),
                    verify=verify,
                    allow_redirects=True,
                )

            if self.cancelled:
                return self.failed_fn(None)

            code = resp.status_code
            body = resp.content or b""

            # Try JSON first (original behavior). :contentReference[oaicite:2]{index=2}
            ret = None
            if body:
                try:
                    ret = json.loads(body.decode("utf-8", "replace"))
                except ValueError:
                    # Fallback: some servers reply with plain text URL
                    txt = body.decode("utf-8", "replace").strip()
                    if txt.startswith("http://") or txt.startswith("https://"):
                        ret = {"url": txt}

            # Error mapping like the original
            error = None
            if code != 200:
                error = getattr(httplib, "responses", {}).get(code, f"HTTP error {code}")
            elif ret is not None and isinstance(ret, dict) and "error" in ret:
                error = ret["error"]
            elif ret is None:
                # Give a tiny preview for diagnostics (still returned as an error)
                preview = body[:200].decode("utf-8", "replace") if body else ""
                error = f"Cannot decode output JSON (first bytes: {preview!r})"

            if self.cancelled:
                return self.failed_fn(None)

            if error:
                return self.failed_fn(DLError("DL service error: " + str(error)))
            else:
                return self.complete_fn(ret)

        except Exception as e:
            if self.cancelled:
                return self.failed_fn(None)
            return self.failed_fn(DLError("DL connection error: " + str(e)))
        finally:
            if mp is not None:
                try:
                    mp.close()
                except Exception:
                    pass

    def cancel(self):
        # Best-effort: marks as cancelled; can’t abort an in-flight libcurl perform via requests-like API.
        self.cancelled = True


class DL(object):
    def __init__(self, service=Service()):
        self.service = service

    def request(self, request, msg, file, asynchronous=False, complete_fn=None, failed_fn=None, progress_fn=None):
        if asynchronous:
            return Request(self, request, msg, file, complete_fn, failed_fn, progress_fn)
        else:
            ret = {}
            complete_fn_ovr = lambda msg: ret.__setitem__('ret', msg)
            failed_fn_ovr = lambda ex: ret.__setitem__('ex', ex)
            req = Request(self, request, msg, file, complete_fn_ovr, failed_fn_ovr, progress_fn)
            req.run()
            if 'ex' in ret:
                if failed_fn is not None:
                    failed_fn(ret['ex'])
                else:
                    raise ret['ex']
            if complete_fn is not None:
                complete_fn(ret['ret'])
            return ret['ret']

    def new_ticket(self, file, params=None, asynchronous=False, complete_fn=None, failed_fn=None, progress_fn=None):
        msg = {}
        if params is None:
            params = TicketParams()
        if params.permanent is not None:
            msg['permanent'] = params.permanent
        if params.total is not None:
            msg['ticket_total'] = params.total
        if params.lastdl is not None:
            msg['ticket_lastdl'] = params.lastdl
        if params.downloads is not None:
            msg['ticket_maxdl'] = params.downloads
        return self.request("newticket", msg, file, asynchronous, complete_fn, failed_fn, progress_fn)

    def new_grant(self, email, params=None, asynchronous=False, complete_fn=None, failed_fn=None, progress_fn=None):
        msg = {'notify': email}
        if params is None:
            params = GrantParams()
        if params.total is not None:
            msg['grant_total'] = params.total
        if params.ticket_params.permanent is not None:
            msg['ticket_permanent'] = params.ticket_params.permanent
        if params.ticket_params.total is not None:
            msg['ticket_total'] = params.ticket_params.total
        if params.ticket_params.lastdl is not None:
            msg['ticket_lastdl'] = params.ticket_params.lastdl
        if params.ticket_params.downloads is not None:
            msg['ticket_maxdl'] = params.ticket_params.downloads
        return self.request("newgrant", msg, None, asynchronous, complete_fn, failed_fn, progress_fn)
