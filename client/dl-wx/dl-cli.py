#!/usr/bin/env python
import configobj
import validate
import argparse
import os.path
import sys
from dl import *

DL_VERSION = "0.10"
DL_AGENT = "dl-cli/" + DL_VERSION


def die(descr, code=1):
    print >> sys.stderr, sys.argv[0] + ": " + descr
    exit(code)


def progress(download_t, download_d, upload_t, upload_d):
    if upload_d > 0:
        print >> sys.stderr, "uploading: {:-7.3f}%\r".format(upload_d * 100 / upload_t),


def main():
    parser = argparse.ArgumentParser(description="Upload a file to DL", epilog=DL_AGENT)
    parser.add_argument('-r', metavar="file", dest="rc", default="~/.dl.rc", help="Use alternate RC file")
    parser.add_argument('file', help="File to upload")
    args = parser.parse_args()

    cfgpath = os.path.expanduser(args.rc)
    cfg = configobj.ConfigObj(cfgpath)
    v = validate.Validator()
    for param in ['user', 'pass', 'url']:
        if param not in cfg:
            die("missing \"{0}\" in configuration file".format(param))
        cfg[param] = v.check('string', cfg[param])
    cfg['verify'] = v.check('boolean', cfg.get('verify', True))

    service = Service(cfg['url'], cfg['user'], cfg['pass'],
                      cfg['verify'], DL_AGENT)
    dl = DL(service)
    try:
        fun = progress if sys.stdout.isatty() else None
        answ = dl.new_ticket(args.file, progress=fun)
        print(answ['url'])
    except DLError as e:
        die(str(e))


if __name__ == "__main__":
    main()
