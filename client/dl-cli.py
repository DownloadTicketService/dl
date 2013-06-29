#!/usr/bin/env python
import configobj
import base64
import validate
import pycurl
import httplib
import StringIO
import json
import argparse
import os.path
import sys

DL_VERSION = "0.10"
DL_AGENT = "dl-cli/" + DL_VERSION


class UploadError(Exception):
    def __init__(self, value):
        self.value = value
    def __str__(self):
        return self.value


def newticket(file, params):
    s = StringIO.StringIO()
    c = pycurl.Curl()
    c.setopt(c.URL, params['url'] + "/newticket")
    c.setopt(c.WRITEFUNCTION, s.write)

    if sys.stdout.isatty():
        def progress(download_t, download_d, upload_t, upload_d):
            if upload_d > 0:
                print >> sys.stderr, "uploading: {:-7.3f}%\r".format(upload_d * 100 / upload_t),
        c.setopt(c.NOPROGRESS, False)
        c.setopt(c.PROGRESSFUNCTION, progress)

    auth = params['user'] + ':' + params['pass']
    c.setopt(c.HTTPAUTH, c.HTTPAUTH_BASIC)
    c.setopt(c.USERPWD, auth)
    c.setopt(c.HTTPHEADER, ['Expect:',
                            'User-agent: ' + DL_AGENT,
                            'X-Authorization: Basic ' + base64.b64encode(auth)])
    c.setopt(c.HTTPPOST, [
        ("file", (c.FORM_FILE, file)),
        ("msg", json.dumps({}))])
    if not params['verify']:
        c.setopt(c.SSL_VERIFYPEER, False)

    try:
        c.perform()
    except pycurl.error as e:
        raise UploadError("Cannot contact DL service: " + e[1])

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
        raise UploadError("Service error: " + error)
    if ret is None:
        raise UploadError("Service error: cannot decode output JSON")

    c.close()
    return ret


def die(descr, code=1):
    print >> sys.stderr, sys.argv[0] + ": " + descr
    exit(code)


def main():
    parser = argparse.ArgumentParser(description="Upload a file to DL", epilog=DL_AGENT)
    parser.add_argument('-r', metavar="file", dest="rc", default="~/.dl.rc", help="Use alternate RC file")
    parser.add_argument('file', help="File to upload")
    args = parser.parse_args()

    cfgpath = os.path.expanduser(args.rc)
    cfg = configobj.ConfigObj(cfgpath)
    v = validate.Validator()
    cfg['verify'] = v.check('boolean', cfg.get('verify', True))

    try:
        answ = newticket(args.file, cfg)
        print(answ['url'])
    except UploadError as e:
        die(str(e))


if __name__ == "__main__":
    main()
