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

function bigBreak {

    DELIM="#####################################################################################################"
    printf "\n\n$DELIM\n"
    printf "########## $1\n"
    printf "$DELIM\n\n"
}

if [ "#$" == "0" ]; then

    bigBreak "No Symfony2 directory passed as an argument"
fi