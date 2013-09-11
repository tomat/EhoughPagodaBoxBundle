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
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 *
 */
abstract class AbstractDoctrineCacheBasedFeature implements FeatureInterface
{
    protected function buildDoctrineCacheDefinition(array $settings, ContainerBuilder $container, $persistentId = null)
    {
        switch ($settings[Configuration::KEY_CACHE_TYPE]) {

            case Configuration::CACHE_TYPE_MEMCACHED:

                return $this->_buildDoctrineMemcachedDefinition(

                    $persistentId,
                    $settings,
                    $container
                );

            case Configuration::CACHE_TYPE_MEMCACHE:

                return $this->_buildDoctrineMemcacheDefinition(

                    $settings,
                    $container
                );

            case Configuration::CACHE_TYPE_REDIS:

                return $this->_buildDoctrineRedisDefinition(

                    $persistentId,
                    $settings,
                    $container
                );

            default:

                return null;
        }
    }

    private function _buildDoctrineMemcachedDefinition($persistentId, array $cacheSettings, ContainerBuilder $container)
    {
        $serverDefinition = new Definition('Memcached');
        $serverDefinition->addArgument($persistentId);
        $serverDefinition->addMethodCall('addServer', $this->_getHostAndPortArray($cacheSettings));

        $serverId = 'ehough_pagoda_box.memcached_instance_' . mt_rand();

        $container->setDefinition($serverId, $serverDefinition);

        $doctrineCacheDefinition = new Definition('Doctrine\Common\Cache\MemcachedCache');
        $doctrineCacheDefinition->addMethodCall('setMemcached', array(new Reference($serverId)));

        return $doctrineCacheDefinition;
    }

    private function _buildDoctrineMemcacheDefinition(array $cacheSettings, ContainerBuilder $container)
    {
        $serverDefinition = new Definition('Memcache');
        $serverDefinition->addMethodCall('addServer', $this->_getHostAndPortArray($cacheSettings));

        $serverId = 'ehough_pagoda_box.memcache_instance_' . mt_rand();

        $container->setDefinition($serverId, $serverDefinition);

        $doctrineCacheDefinition = new Definition('Doctrine\Common\Cache\MemcacheCache');
        $doctrineCacheDefinition->addMethodCall('setMemcache', array(new Reference($serverId)));

        return $doctrineCacheDefinition;
    }

    private function _buildDoctrineRedisDefinition($persistentId, array $cacheSettings, ContainerBuilder $container)
    {
        $serverDefinition = new Definition('Redis');
        $connectParams   = array_merge($this->_getHostAndPortArray($cacheSettings), array(

            30,
            $persistentId
        ));
        $serverDefinition->addMethodCall('pconnect', $connectParams);

        $serverId = 'ehough_pagoda_box.redis_instance_' . mt_rand();

        $container->setDefinition($serverId, $serverDefinition);

        $annotationsCacheServiceDefinition = new Definition('Doctrine\Common\Cache\RedisCache');
        $annotationsCacheServiceDefinition->addMethodCall('setRedis', array(new Reference($serverId)));

        return $annotationsCacheServiceDefinition;
    }

    private function _getHostAndPortArray(array $arr)
    {
        $toReturn = array();

        foreach ($this->_getRequiredEnvironmentVariables($arr) as $variable) {

            if ($this->_stringEndsWith($variable, '_PORT')) {

                $toReturn[] = intval(getenv($variable));

            } else {

                $toReturn[] = getenv($variable);
            }
        }

        return $toReturn;
    }

    private function _getRequiredEnvironmentVariables(array $arr)
    {
        $prefix = $arr[Configuration::KEY_CACHE_ID];

        return array(

            $prefix . '_HOST',
            $prefix . '_PORT',
        );
    }

    private function _stringEndsWith($haystack, $needle)
    {
        return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
    }
}
