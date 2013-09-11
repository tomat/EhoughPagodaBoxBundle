#!/bin/bash
#
# Copyright 2013 Eric D. Hough (http://ehough.com)
#
# This file is part of ehough/pagodabox-bundle.
#
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this
# file, You can obtain one at http://mozilla.org/MPL/2.0/.
#

MY_DIR=`dirname $0`
source $MY_DIR/init.sh

if [ ! -f ~/composer.phar ]
    then
        exit 1
fi

bigBreak "Installing dependencies defined in $1/composer.json into $1/vendor"

cd "$1"

php ~/composer.phar install

if [ $? -ne 0 ]; then
    bigBreak "Installing dependencies failed"
    exit 1
fi

bigBreak "Dumping and optimizing composer's autoloader"

php ~/composer.phar dump-autoload --optimize

if [ $? -ne 0 ]; then
    bigBreak "Dumping and optimizing composer's autoloader failed"
    exit 1
fi

bigBreak "Done installing composer dependencies"

exit 0