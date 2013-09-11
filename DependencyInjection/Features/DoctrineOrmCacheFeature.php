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
class DoctrineOrmCacheFeature extends AbstractDoctrineCacheBasedFeature
{
    const CACHE_MAP_ID = 'ehough_pagoda_box.doctrine_orm_caching_map';

    public function shouldAct(array $processedConfiguration, ContainerBuilder $container)
    {
        if (!isset($processedConfiguration[Configuration::KEY_DOCTRINE])) {

            return false;
        }


        if (!isset($processedConfiguration[Configuration::KEY_DOCTRINE][Configuration::KEY_DOCTRINE_ORM])
            || !isset($processedConfiguration[Configuration::KEY_DOCTRINE][Configuration::KEY_DOCTRINE_ORM][Configuration::KEY_DOCTRINE_ORM_CACHING])) {

            return false;
        }

        return true;
    }

    public function act(array $processedConfiguration, ContainerBuilder $container)
    {
        $cacheMap = $processedConfiguration[Configuration::KEY_DOCTRINE][Configuration::KEY_DOCTRINE_ORM][Configuration::KEY_DOCTRINE_ORM_CACHING];

        $toSet = array();

        foreach ($cacheMap as $emName => $cacheMaps) {

            $validCacheNames    = array(
                Configuration::KEY_DOCTRINE_ORM_CACHETYPE_METADATA,
                Configuration::KEY_DOCTRINE_ORM_CACHETYPE_QUERY,
                Configuration::KEY_DOCTRINE_ORM_CACHETYPE_RESULT
            );
            $cacheDefinitionIds = array();

            foreach ($cacheMaps as $cacheName => $cacheSettings) {

                if (!in_array($cacheName, $validCacheNames)) {

                    continue;
                }

                $persistentId    = 'ehough_pagoda_box.doctrine_' . $emName . '_' . $cacheName . '_cache';
                $cacheDefinition = $this->buildDoctrineCacheDefinition($cacheSettings, $container, $persistentId);
                $cacheServiceId  = $persistentId . '_' . mt_rand();

                $container->setDefinition($cacheServiceId, $cacheDefinition);

                $cacheDefinitionIds[$cacheName] = $cacheServiceId;
            }

            if (count($cacheDefinitionIds) > 0) {

                $toSet[$emName] = $cacheDefinitionIds;
            }
        }

        if (count($toSet) > 0) {

            $container->setParameter(self::CACHE_MAP_ID, $toSet);
        }
    }
}
