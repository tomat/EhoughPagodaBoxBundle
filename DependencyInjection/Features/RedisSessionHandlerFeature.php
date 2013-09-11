<?php
/**
 * Copyright 2013 Eric D. Hough (http://ehough.com)
 *
 * This file is part of ehough/pagodabox-bundle.
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace Ehough\Bundle\PagodaBoxBundle\DependencyInjection\Features;

use Ehough\Bundle\PagodaBoxBundle\DependencyInjection\Configuration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 *
 */
class RedisSessionHandlerFeature implements FeatureInterface
{
    const SERVICE_ID_SESSION_HANDLER        = 'ehough_pagoda_box.native_session_handler';
    const ALIAS_ID_ORIGINAL_SESSION_HANDLER = 'session.handler';

    public function shouldAct(array $processedConfiguration, ContainerBuilder $container)
    {
        if (!isset($processedConfiguration[Configuration::KEY_STORE_SESSIONS_IN_REDIS])) {

            //omitted?
            return false;
        }

        if (!$processedConfiguration[Configuration::KEY_STORE_SESSIONS_IN_REDIS]) {

            //explicit false
            return false;
        }

        if (!class_exists('Redis', false)) {

            //no Redis extension loaded
            return false;
        }

        if (ini_get('session.save_handler') !== 'redis') {

            //didn't set Redis as the session handler
            return false;
        }

        $sessionSavePath = ini_get('session.save_path');

        if (preg_match_all('~^tcp://[^:]+:6379/?$~', $sessionSavePath, $matches) !== 1) {

            //invalid session save path
            return false;
        }

        return true;
    }

    public function act(array $processedConfiguration, ContainerBuilder $container)
    {
        /**
         * Register a native session handler.
         */
        $container->register(

            self::SERVICE_ID_SESSION_HANDLER,
            'Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeSessionHandler'
        );
    }
}
