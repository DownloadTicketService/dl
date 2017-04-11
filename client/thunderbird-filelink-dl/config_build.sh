#!/bin/bash

# Build config for the build script, build.sh. Look there for more info.

APP_NAME=thunderbird-filelink-dl
CHROME_PROVIDERS="chrome"
CLEAN_UP=1
ROOT_FILES=
ROOT_DIRS="components"
BEFORE_BUILD="/usr/lib/thunderbird-devel/sdk/bin/typelib.py -I /usr/share/idl/thunderbird components/nsDL.idl -o components/nsDL.xpt"
AFTER_BUILD=
