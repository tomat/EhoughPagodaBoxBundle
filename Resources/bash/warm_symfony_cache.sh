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

bigBreak "Dumping assetic assets"
php "$1/app/console" "assetic:dump" --env=prod

bigBreak "Clearing any Symfony cache"
php "$1/app/console" "cache:clear" --env=prod

bigBreak "Warming up the Symfony cache"
php "$1/app/console" "cache:warmup" --env=prod

bigBreak "Triggering an initial HTTP request for Symfony to finish warming the cache"
php "$1/web/app.php" > /dev/null

bigBreak "Symfony's cache is fully warmed up"