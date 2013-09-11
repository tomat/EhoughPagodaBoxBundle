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

if [ ! -f "~/composer.phar" ]
    then
        bigBreak "composer doesn't exist. Downloading it now..."
        curl -s http://getcomposer.org/composer.phar > ~/composer.phar
fi

if [ ! -f ~/composer.phar ]; then

        bigBreak "Could not install composer"
        exit 1

    else
        bigBreak "composer is installed and ready"
fi

if [ "$#" == "2" ]; then

    if [ ! -d ~/.composer ]; then

        mkdir -p ~/.composer
    fi

    echo "
        {
            \"config\": {
                \"github-oauth\": {
                    \"github.com\" : \"$1\"
                }
            }
        }
        " >> ~/.composer/config.json
fi

exit 0