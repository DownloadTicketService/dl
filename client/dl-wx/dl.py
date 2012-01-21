#!/usr/bin/env python
import pycurl
import httplib
import StringIO
import json
from threading import Thread


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
    def __init__(self, perm=None, total_days=None,
                 hours_after_dl=None, downloads=None):
        self.perm = perm
        self.total_days = total_days
        self.hours_after_dl = hours_after_dl
        self.downloads = downloads


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
        s = StringIO.StringIO()
        c = pycurl.Curl()
        c.setopt(c.URL, self.dl.service.url + "/" + self.request)
        c.setopt(c.WRITEFUNCTION, s.write)
        c.setopt(c.HTTPAUTH, c.HTTPAUTH_BASIC)
        c.setopt(c.USERPWD, self.dl.service.username + ':' + self.dl.service.password)
        c.setopt(c.HTTPHEADER, ['Expect:', 'User-agent: ' + self.dl.service.agent])

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
        while 1:
            while 1:
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
        if error != "":
            return self.failed_fn(DLError("DL connection error: " + error))

        ret = None
        if s.tell():
            s.seek(0)
            try:
                ret = json.load(s)
            except ValueError:
                pass

        if code != httplib.OK:
            error = httplib.responses[code]
        elif ret is not None and 'error' in ret:
            error = ret['error']
        elif ret is None:
            error = "Cannot decode output JSON"

        if error != "":
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

    def new_ticket(self, file, params=TicketParams(), async=False, complete_fn=None, failed_fn=None, progress_fn=None):
        msg = {}
        if params.perm is not None:
            msg['perm'] = params.perm
        if params.total_days is not None:
            msg['dn'] = params.total_days
        if params.hours_after_dl is not None:
            msg['hra'] = params.hours_after_dl
        if params.downloads is not None:
            msg['dln'] = params.downloads
        return self.request("newticket", msg, file, async, complete_fn, failed_fn, progress_fn)
