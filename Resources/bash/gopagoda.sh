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

$MY_DIR/install_composer.sh $2

if [ $? -ne 0 ]; then
    bigBreak "Composer installation failed. Stopping."
    exit 1
fi

$MY_DIR/install_dependencies.sh $1

if [ $? -ne 0 ]; then
    bigBreak "Dependency installation failed. Stopping."
    exit 1
fi

$MY_DIR/warm_symfony_cache.sh $1