from __future__ import unicode_literals, print_function, generators, absolute_import

import pycurl
import binascii
import json
import io
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

    def _progress(self, download_t, download_d, upload_t, upload_d):
        self.progress_fn(download_t, download_d, self.speed_download,
                         upload_t, upload_d, self.speed_upload)

    def run(self):
        s = BytesIO()
        c = pycurl.Curl()
        c.setopt(c.URL, self.dl.service.url + "/" + self.request)
        c.setopt(c.WRITEFUNCTION, s.write)

        auth = self.dl.service.username + ':' + self.dl.service.password
        xauth = binascii.b2a_base64(auth.encode('utf8')).decode('ascii')[:-1]

        c.setopt(c.HTTPAUTH, c.HTTPAUTH_BASIC)
        c.setopt(c.USERPWD, auth)
        c.setopt(c.HTTPHEADER, ['Expect:',
                                'User-agent: ' + self.dl.service.agent,
                                'X-Authorization: Basic ' + xauth])

        if self.file or self.msg is not None:
            post_data = []
            if self.file:
                post_data.append(("file", (c.FORM_FILE, self.file)))
            if self.msg is not None:
                post_data.append(("msg", json.dumps(self.msg)))
            c.setopt(c.HTTPPOST, post_data)

        if not self.dl.service.verify:
            c.setopt(c.SSL_VERIFYPEER, False)

        if self.progress_fn is not None:
            self.speed_download = 0
            self.speed_upload = 0
            c.setopt(c.NOPROGRESS, False)
            c.setopt(c.PROGRESSFUNCTION, self._progress)

        m = pycurl.CurlMulti()
        m.add_handle(c)
        num_handles = 1
        while True:
            while True:
                ret, num_handles = m.perform()
                if ret != pycurl.E_CALL_MULTI_PERFORM:
                    break
            if num_handles == 0 or self.cancelled:
                break
            m.select(1.0)
            if self.progress_fn is not None:
                self.speed_download = c.getinfo(c.SPEED_DOWNLOAD)
                self.speed_upload = c.getinfo(c.SPEED_UPLOAD)
        m.remove_handle(c)
        code = c.getinfo(pycurl.HTTP_CODE)
        error = c.errstr()
        c.close()

        if self.cancelled:
            return self.failed_fn(None)
        if error:
            return self.failed_fn(DLError("DL connection error: " + error))

        ret = None
        if s.tell():
            s.seek(0)
            try:
                ret = json.load(io.TextIOWrapper(s, 'utf8'))
            except ValueError:
                pass

        if code != httplib.OK:
            error = httplib.responses[code]
        elif ret is not None and 'error' in ret:
            error = ret['error']
        elif ret is None:
            error = "Cannot decode output JSON"

        if error:
            return self.failed_fn(DLError("DL service error: " + error))
        else:
            return self.complete_fn(ret)

    def cancel(self):
        self.cancelled = True


class DL(object):
    def __init__(self, service=Service()):
        self.service = service

    def request(self, request, msg, file, async=False, complete_fn=None, failed_fn=None, progress_fn=None):
        if async:
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

    def new_ticket(self, file, params=None, async=False, complete_fn=None, failed_fn=None, progress_fn=None):
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
        return self.request("newticket", msg, file, async, complete_fn, failed_fn, progress_fn)

    def new_grant(self, email, params=None, async=False, complete_fn=None, failed_fn=None, progress_fn=None):
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
        return self.request("newgrant", msg, None, async, complete_fn, failed_fn, progress_fn)
