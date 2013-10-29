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

namespace Ehough\Bundle\PagodaBoxBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Primary bundle configuration.
 */
class Configuration implements ConfigurationInterface
{
    const KEY_ANNOTATIONS_CACHE               = 'annotations_cache';
    const KEY_CACHE_ID                        = 'pagoda_env_id';
    const KEY_CACHE_TYPE                      = 'type';
    const KEY_DOCTRINE                        = 'doctrine';
    const KEY_DOCTRINE_DBAL                   = 'dbal';
    const KEY_DOCTRINE_DBAL_CONNECTIONS       = 'connections';
    const KEY_DOCTRINE_ORM                    = 'orm';
    const KEY_DOCTRINE_ORM_CACHING            = 'caching';
    const KEY_DOCTRINE_ORM_CACHETYPE_METADATA = 'metadata';
    const KEY_DOCTRINE_ORM_CACHETYPE_QUERY    = 'query';
    const KEY_DOCTRINE_ORM_CACHETYPE_RESULT   = 'result';
    const KEY_PAGODA_BOX                      = 'pagoda_box';
    const KEY_STORE_SESSIONS_IN_REDIS         = 'store_sessions_in_redis';

    const CACHE_TYPE_MEMCACHED = 'memcached';
    const CACHE_TYPE_MEMCACHE  = 'memcache';
    const CACHE_TYPE_REDIS     = 'redis';

    /**
     * Get config tree
     *
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder                = new TreeBuilder();
        $invalidDbIdentifier        = function ($candidate) {

            return Configuration::_invalidId('DB', $candidate);
        };
        $invalidAnnotationCache = function ($candidate) {

            switch ($candidate) {

                case Configuration::CACHE_TYPE_MEMCACHE:

                    return !class_exists('\Memcache', false)
                        || !class_exists('\Doctrine\Common\Cache\MemcacheCache', true);

                case Configuration::CACHE_TYPE_MEMCACHED:

                    return !class_exists('\Memcached', false)
                        || !class_exists('\Doctrine\Common\Cache\MemcachedCache', true);

                default:

                    return true;
            }
        };

        $treeBuilder->root(Configuration::KEY_PAGODA_BOX)->children()

            ->booleanNode(Configuration::KEY_STORE_SESSIONS_IN_REDIS)
                ->defaultFalse()
            ->end()
            ->arrayNode(Configuration::KEY_ANNOTATIONS_CACHE)
                ->children()
                    ->scalarNode(Configuration::KEY_CACHE_TYPE)
                        ->isRequired()
                        ->validate()
                            ->ifTrue($invalidAnnotationCache)
                            ->thenInvalid('%s is not a valid annotation cache type. Must be "memcache" or "memcached". The corresponding PHP extension must also be loaded, along with the corresponding Doctrine cache class.')
                        ->end()
                    ->end()
                    ->append(Configuration::_appendCacheEnvId())
                ->end()
            ->end()
            ->arrayNode(Configuration::KEY_DOCTRINE)
                ->children()
                    ->arrayNode(Configuration::KEY_DOCTRINE_DBAL)
                        ->children()
                            ->arrayNode(Configuration::KEY_DOCTRINE_DBAL_CONNECTIONS)
                                ->useAttributeAsKey('name')
                                ->prototype('scalar')
                                    ->isRequired()
                                    ->validate()
                                        ->ifTrue($invalidDbIdentifier)
                                        ->thenInvalid('%s is not a valid Pagoda Box database identifier. Should be DB1, DB2, etc.')
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode(Configuration::KEY_DOCTRINE_ORM)
                        ->children()
                            ->arrayNode(Configuration::KEY_DOCTRINE_ORM_CACHING)
                                ->useAttributeAsKey('name')
                                ->prototype('array')
                                    ->children()
                                        ->append(Configuration::_appendDoctrineCache(Configuration::KEY_DOCTRINE_ORM_CACHETYPE_METADATA))
                                        ->append(Configuration::_appendDoctrineCache(Configuration::KEY_DOCTRINE_ORM_CACHETYPE_QUERY))
                                        ->append(Configuration::_appendDoctrineCache(Configuration::KEY_DOCTRINE_ORM_CACHETYPE_RESULT))
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }

    public static function _appendDoctrineCache($name)
    {
        $builder         = new TreeBuilder();
        $node            = $builder->root($name);
        $invalidCacheType = function ($candidate) {

            switch ($candidate) {

                case Configuration::CACHE_TYPE_MEMCACHE:

                    return !class_exists('\Memcache', false)
                    || !class_exists('\Doctrine\Common\Cache\MemcacheCache', true);

                case Configuration::CACHE_TYPE_MEMCACHED:

                    return !class_exists('\Memcached', false)
                    || !class_exists('\Doctrine\Common\Cache\MemcachedCache', true);

                default:

                    return true;
            }
        };

        $node->children()
            ->scalarNode(Configuration::KEY_CACHE_TYPE)
                ->isRequired()
                ->validate()
                    ->ifTrue($invalidCacheType)
                    ->thenInvalid('%s is not a valid cache type for a Doctrine cache. Must be "memcache" or "memcached", and the corresponding PHP extension and Doctrine class must be loaded.')
                ->end()
            ->end()
            ->append(Configuration::_appendCacheEnvId());

        return $node;
    }

    public static function _appendCacheEnvId()
    {
        $node           = new ScalarNodeDefinition(Configuration::KEY_CACHE_ID);
        $invalidCacheId = function ($candidate) {

            return Configuration::_invalidId('CACHE', $candidate);
        };

        $node->isRequired()
            ->validate()
                ->ifTrue($invalidCacheId)
                ->thenInvalid('%s is not a valid Pagoda Box cache identifier. Should be CACHE1, CACHE2, etc., and the corresponding Pagoda Box environment variables must be present.')
        ->end();

        return $node;
    }

    public static function _invalidId($prefix, $candidate)
    {
        if (preg_match_all('/^' . $prefix . '[1-9]+[0-9]*$/', $candidate, $matches) !== 1) {

            return true;
        }

        return getenv($candidate . '_HOST') === false || getenv($candidate . '_PORT') === false;
    }
}
