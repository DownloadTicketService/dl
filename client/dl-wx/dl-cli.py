#!/usr/bin/env python
import configobj
import validate
import argparse
import os.path
import sys
from dl import *

DL_VERSION = "0.13"
DL_AGENT = "dl-cli/" + DL_VERSION


def die(descr, code=1):
    print >> sys.stderr, sys.argv[0] + ": " + descr
    exit(code)


def progress(download_t, download_d, download_s, upload_t, upload_d, upload_s):
    if upload_d > 0:
        prc = upload_d * 100 / upload_t
        ks = upload_s / 1024
        print >> sys.stderr, "uploading: {:-7.3f}% {:-10.3f}KiB/s\r".format(prc, ks),


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
        if args.file:
            fun = progress if sys.stdout.isatty() else None
            answ = dl.new_ticket(args.file, progress_fn=fun)
        else:
            answ = dl.new_grant(args.grant)
        print(answ['url'])
    except KeyboardInterrupt:
	pass
    except DLError as e:
        die(str(e))


if __name__ == "__main__":
    main()
