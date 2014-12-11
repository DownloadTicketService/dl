#!/usr/bin/env python
import ConfigParser
import base64
import pycurl
import httplib
import StringIO
import json
import argparse
import os.path
import sys
import getpass

DL_VERSION = "0.13"
DL_AGENT = "dl-cli/" + DL_VERSION
CFG_SECTION = 'DEFAULT'


class DefaultSection(object):
    def __init__(self, path):
        self.fp = open(path)
        self.readline = self.head

    def head(self):
        self.readline = self.fp.readline
        return "[DEFAULT]\n"


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


def newgrant(email, params):
    s = StringIO.StringIO()
    c = pycurl.Curl()
    c.setopt(c.URL, params['url'] + "/newgrant")
    c.setopt(c.WRITEFUNCTION, s.write)

    auth = params['user'] + ':' + params['pass']
    c.setopt(c.HTTPAUTH, c.HTTPAUTH_BASIC)
    c.setopt(c.USERPWD, auth)
    c.setopt(c.HTTPHEADER, ['Expect:',
                            'User-agent: ' + DL_AGENT,
                            'X-Authorization: Basic ' + base64.b64encode(auth)])

    msg = {'notify': email}
    c.setopt(c.HTTPPOST, [("msg", json.dumps(msg))])
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
    parser.add_argument('-r', metavar="file", dest="rc",
                        default="~/.dl.rc", help="Use alternate RC file")
    group = parser.add_mutually_exclusive_group(required=True)
    group.add_argument('-g', metavar="email", dest="grant",
                       help="Generate a grant with notification sent to 'email'")
    group.add_argument('file', nargs='?', help="File to upload")
    args = parser.parse_args()

    cfgpath = os.path.expanduser(args.rc)
    cp = ConfigParser.RawConfigParser()
    cp.readfp(DefaultSection(cfgpath))
    
    cfg = {'url' : cp.get(CFG_SECTION, 'url'),
           'user': cp.get(CFG_SECTION, 'user'),
           'pass': cp.get(CFG_SECTION, 'pass'),
           'verify': cp.getboolean(CFG_SECTION, 'verify')}
          
    # Prompt for password if not in config file
    if not cfg['pass']:
        cfg['pass'] = getpass.getpass('Password for ' + cfg['user'] + ':')
        
    try:
        if args.file:
            answ = newticket(args.file, cfg)
        else:
            answ = newgrant(args.grant, cfg)
        print(answ['url'])
    except UploadError as e:
        die(str(e))


if __name__ == "__main__":
    main()
