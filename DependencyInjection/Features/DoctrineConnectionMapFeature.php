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
class DoctrineConnectionMapFeature implements FeatureInterface
{
    const CONNECTION_MAP_ID   = 'ehough_pagoda_box.doctrine_connection_map';
    const CONNECTION_KEY_HOST = 'host';
    const CONNECTION_KEY_PORT = 'port';
    const CONNECTION_KEY_USER = 'user';
    const CONNECTION_KEY_PASS = 'pass';
    const CONNECTION_KEY_NAME = 'name';

    public function shouldAct(array $processedConfiguration, ContainerBuilder $container)
    {
        if (!isset($processedConfiguration[Configuration::KEY_DOCTRINE])) {

            return false;
        }

        if (!isset($processedConfiguration[Configuration::KEY_DOCTRINE][Configuration::KEY_DOCTRINE_DBAL])
            || !isset($processedConfiguration[Configuration::KEY_DOCTRINE][Configuration::KEY_DOCTRINE_DBAL][Configuration::KEY_DOCTRINE_DBAL_CONNECTIONS])) {

            return false;
        }

        return true;
    }

    public function act(array $processedConfiguration, ContainerBuilder $container)
    {
        $connectionMap = $processedConfiguration[Configuration::KEY_DOCTRINE][Configuration::KEY_DOCTRINE_DBAL][Configuration::KEY_DOCTRINE_DBAL_CONNECTIONS];

        $toSet = array();

        foreach ($connectionMap as $connectionName => $pagodaboxEnvironmentVariableName) {

            $toSet[$connectionName] = array(

                self::CONNECTION_KEY_HOST => getenv($pagodaboxEnvironmentVariableName . '_HOST'),
                self::CONNECTION_KEY_PORT => intval(getenv($pagodaboxEnvironmentVariableName . '_PORT')),
                self::CONNECTION_KEY_USER => getenv($pagodaboxEnvironmentVariableName . '_USER'),
                self::CONNECTION_KEY_PASS => getenv($pagodaboxEnvironmentVariableName . '_PASS'),
                self::CONNECTION_KEY_NAME => getenv($pagodaboxEnvironmentVariableName . '_NAME'),
            );
        }

        if (count($toSet) > 0) {

            $container->setParameter(self::CONNECTION_MAP_ID, $toSet);
        }
    }
}
