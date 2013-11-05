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
# This file is meant to be operated on the Pagoda Box platform only.

function performComposerInstallation () {

    wget --quiet https://raw.github.com/ehough/EhoughPagodaBoxBundle/master/Resources/bash/composer_installer.sh

    if [ ! -f composer_installer.sh ]; then

        printf "Could not download composer bootstrap script\n"
        exit 1
    fi

    source ./composer_installer.sh $1 $2

    if [ $? -ne 0 ]; then

       	printf "Failed to install composer dependencies\n"
       	exit 1
    fi
}

function runConsoleCommand() {

    php "./app/console" "$1" --env=prod

    if [ $? -ne 0 ]; then

    	log "$1 failed"
    	exit 1
    fi
}

function dumpAssetic () {

	log "Dumping assetic assets"
	php "./app/console" "assetic:dump" --env=prod

	if [ $? -ne 0 ]; then

		log "Dumping of assetic assets failed"
		exit 1
	fi
}

function warmSymfonyCache () {

	log "Warming up the Symfony cache"

	runConsoleCommand "cache:warmup"
}

function triggerInitialHttpRequest () {

	log "Triggering an initial HTTP request for Symfony to finish warming the cache"
	php "./web/app.php" > /dev/null

	if [ $? -ne 0 ]; then

		log "Initial HTTP request failed"
		exit 1
	fi

	log "Symfony's cache is fully warmed up"
}

performComposerInstallation $1 $2
dumpAssetic
warmSymfonyCache
triggerInitialHttpRequest

log "Your Symfony2 app is ready!"