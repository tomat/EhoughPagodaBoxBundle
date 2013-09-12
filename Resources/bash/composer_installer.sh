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

function log () {

	DELIM="#####################################################################################################"
	printf "\n\n$DELIM\n"
	printf "########## $1\n"
	printf "$DELIM\n\n"
}

function checkArgs () {

	if [ ! -d "$1" ]; then

		log "No directory passed as an argument"
		exit 1
	fi

	log "Installing composer-based app at $1"
}

function downloadComposer () {

	if [ ! -f "~/composer.phar" ]; then

		log "composer doesn't exist. Downloading it now..."
		curl -sS http://getcomposer.org/composer.phar > ~/composer.phar
	fi

	if [ ! -f ~/composer.phar ]; then

		log "Could not install composer"
		exit 1

	else

		log "composer is installed and ready"
	fi
}

function configureComposer () {

	if [ "$1" != "" ]; then

		if [ ! -d ~/.composer ]; then

			mkdir -p ~/.composer
		fi

		if [ -f ~/.composer/config.json ]; then

			return
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
}

function installDependencies () {

	PWD=`pwd`

	log "Installing dependencies defined in $PWD/composer.json into $PWD/vendor"

	php ~/composer.phar install

	if [ $? -ne 0 ]; then

		log "Dependency installation failed"
		exit 1
	fi
}

function optimizeClassloader () {

	log "Dumping and optimizing composer's autoloader"

	php ~/composer.phar dump-autoload --optimize

	if [ $? -ne 0 ]; then

		log "Dumping and optimizing composer's autoloader failed"
		exit 1
	fi
}

checkArgs $1
downloadComposer
configureComposer $2
cd $1
installDependencies
optimizeClassloader

log "Composer tasks complete for $1"