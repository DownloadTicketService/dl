#!/usr/bin/env python
import configobj
import pycurl
import httplib
import StringIO
import json
import os.path
import sys


class UploadError(Exception):
    def __init__(self, value):
        self.value = value
    def __str__(self):
        return self.value


def progress(download_t, download_d, upload_t, upload_d):
    if upload_d > 0:
        print >> sys.stderr, "uploading: {:-7.3f}%\r".format(upload_d * 100 / upload_t),


def newticket(file, params):
    s = StringIO.StringIO()
    c = pycurl.Curl()
    c.setopt(c.URL, params['url'] + "/newticket")
    c.setopt(c.WRITEFUNCTION, s.write)

    if sys.stdout.isatty():
        c.setopt(c.NOPROGRESS, False)
        c.setopt(c.PROGRESSFUNCTION, progress)

    c.setopt(c.HTTPPOST,
             [("file", (c.FORM_FILE, file)),
              ("auth", json.dumps({"user": params['user'], "pass": params['pass']})),
              ("msg", json.dumps({}))])
    c.setopt(c.HTTPHEADER, ['Expect:', 'User-agent: dl-cli'])
    if not params['verify']:
        c.setopt(c.SSL_VERIFYPEER, False)

    try:
        c.perform()
    except pycurl.error as e:
        raise UploadError("Cannot contact DL service: " + e[1])

    ret = None
    if s.tell():
        try:
            s.seek(0)
            ret = json.load(s)
        except ValueError:
            pass

    code = c.getinfo(pycurl.HTTP_CODE)
    if code != httplib.OK:
        error = httplib.responses[code]
        if ret is not None and 'error' in ret:
            error = ret['error']
        raise UploadError("Service error: " + error)
    if ret is None:
        raise UploadError("Server error: cannot decode output JSON");

    c.close()
    return ret


if __name__ == "__main__":
    cfgpath = os.path.expanduser("~/.dl.rc")
    cfg = configobj.ConfigObj(cfgpath)
    if 'verify' not in cfg:
        cfg['verify'] = False
    try:
        answ = newticket(sys.argv[1], cfg)
        print answ['url']
    except Exception as e:
        print >> sys.stderr, sys.argv[0] + ": " + str(e)
        exit(1)
