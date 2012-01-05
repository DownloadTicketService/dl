#!/usr/bin/env python
import pycurl
import httplib
import StringIO
import json


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
    pass


class DL(object):
    def __init__(self, service=Service()):
        self.service = service

    def new_ticket(self, file, params=TicketParams(), progress=None):
        s = StringIO.StringIO()
        c = pycurl.Curl()
        c.setopt(c.URL, self.service.url + "/newticket")
        c.setopt(c.WRITEFUNCTION, s.write)

        if progress is not None:
            c.setopt(c.NOPROGRESS, False)
            c.setopt(c.PROGRESSFUNCTION, progress)

        c.setopt(c.HTTPAUTH, c.HTTPAUTH_BASIC)
        c.setopt(c.USERPWD, self.service.username + ':' + self.service.password)
        c.setopt(c.HTTPPOST, [
            ("file", (c.FORM_FILE, file)),
            ("auth", json.dumps({
                "user": self.service.username, "pass": self.service.password})),
            ("msg", json.dumps({}))])
        c.setopt(c.HTTPHEADER, ['Expect:', 'User-agent: ' + self.service.agent])
        if not self.service.verify:
            c.setopt(c.SSL_VERIFYPEER, False)

        try:
            c.perform()
        except pycurl.error as e:
            raise DLError("Cannot contact DL service: " + e[1])

        ret = None
        if s.tell():
            s.seek(0)
            try:
                ret = json.load(s)
            except ValueError:
                pass

        code = c.getinfo(pycurl.HTTP_CODE)
        if code != httplib.OK:
            error = httplib.responses[code]
        if ret is not None and 'error' in ret:
            error = ret['error']
            raise DLError("Service error: " + error)
        if ret is None:
            raise DLError("Service error: cannot decode output JSON")

        c.close()
        return ret
