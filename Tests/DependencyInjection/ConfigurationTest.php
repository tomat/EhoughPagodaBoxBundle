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

namespace Ehough\Bundle\PagodaBoxBundle\Tests\DependencyInjection;

use Ehough\Bundle\PagodaBoxBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testDoctrineOrmResultCacheInvalidType()
    {
        $this->setExpectedException(

            'Symfony\Component\Config\Definition\Exception\InvalidConfigurationException',
            'Invalid configuration for path "pagoda_box.doctrine.orm.caching.default.result.type": "syz" is not a valid cache type for a Doctrine cache. Must be "memcache" or "memcached", and the corresponding PHP extension and Doctrine class must be loaded.'
        );

        $configs = array(
            array(
                Configuration::KEY_DOCTRINE => array(
                    Configuration::KEY_DOCTRINE_ORM => array(
                        Configuration::KEY_DOCTRINE_ORM_CACHING => array(
                            'default' => array(
                                Configuration::KEY_DOCTRINE_ORM_CACHETYPE_RESULT => array(
                                    Configuration::KEY_CACHE_TYPE => 'syz',
                                    Configuration::KEY_CACHE_ID   => 'CACHE12'
                                )
                            )
                        )
                    )
                )
            )
        );

        putenv('CACHE12_HOST=some host');
        putenv('CACHE12_PORT=12');

        $this->process($configs);
    }

    public function testDoctrineOrmResultCache()
    {
        $configs = array(
            array(
                Configuration::KEY_DOCTRINE => array(
                    Configuration::KEY_DOCTRINE_ORM => array(
                        Configuration::KEY_DOCTRINE_ORM_CACHING => array(
                            'default' => array(
                                Configuration::KEY_DOCTRINE_ORM_CACHETYPE_RESULT => array(
                                    Configuration::KEY_CACHE_TYPE => Configuration::CACHE_TYPE_MEMCACHED,
                                    Configuration::KEY_CACHE_ID   => 'CACHE12'
                                )
                            )
                        )
                    )
                )
            )
        );

        putenv('CACHE12_HOST=some host');
        putenv('CACHE12_PORT=12');

        $processed = $this->process($configs);

        $map = $processed[Configuration::KEY_DOCTRINE][Configuration::KEY_DOCTRINE_ORM][Configuration::KEY_DOCTRINE_ORM_CACHING];

        $expected = array(
            'default' => array(
                Configuration::KEY_DOCTRINE_ORM_CACHETYPE_RESULT => array(
                    Configuration::KEY_CACHE_TYPE => Configuration::CACHE_TYPE_MEMCACHED,
                    Configuration::KEY_CACHE_ID   => 'CACHE12'
                )
            )
        );

        $this->assertEquals($expected, $map);
    }

    public function testDoctrineOrmEmptyEmCache()
    {
        $configs = array(
            array(
                Configuration::KEY_DOCTRINE => array(
                    Configuration::KEY_DOCTRINE_ORM => array(
                        Configuration::KEY_DOCTRINE_ORM_CACHING => array(
                            'default' => array(

                            )
                        )
                    )
                )
            )
        );

        $processed = $this->process($configs);

        $map = $processed[Configuration::KEY_DOCTRINE][Configuration::KEY_DOCTRINE_ORM][Configuration::KEY_DOCTRINE_ORM_CACHING];

        $this->assertEquals(array('default' => array()), $map);
    }

    public function testDoctrineOrmEmptyCache()
    {
        $configs = array(
            array(
                Configuration::KEY_DOCTRINE => array(
                    Configuration::KEY_DOCTRINE_ORM => array(
                        Configuration::KEY_DOCTRINE_ORM_CACHING => array(

                        )
                    )
                )
            )
        );

        $processed = $this->process($configs);

        $map = $processed[Configuration::KEY_DOCTRINE][Configuration::KEY_DOCTRINE_ORM][Configuration::KEY_DOCTRINE_ORM_CACHING];

        $this->assertEquals(array(), $map);
    }

    public function testDoctrineOrmCachingOnly()
    {
        $this->setExpectedException(

            'Symfony\Component\Config\Definition\Exception\InvalidConfigurationException',
            'Unrecognized options "0" under "pagoda_box.doctrine.orm"'
        );

        $configs = array(
            array(
                Configuration::KEY_DOCTRINE => array(
                    Configuration::KEY_DOCTRINE_ORM => array(
                        Configuration::KEY_DOCTRINE_ORM_CACHING
                    )
                )
            )
        );

        $this->process($configs);
    }

    public function testConnectionMapBadId2()
    {
        $this->setExpectedException(

            'Symfony\Component\Config\Definition\Exception\InvalidConfigurationException',
            'Invalid configuration for path "pagoda_box.doctrine.dbal.connections.default": "DBx" is not a valid Pagoda Box database identifier. Should be DB1, DB2, etc.'
        );

        $configs = array(
            array(
                Configuration::KEY_DOCTRINE => array(
                    Configuration::KEY_DOCTRINE_DBAL => array(
                        'connections' => array(

                            'default' => 'DBx',
                        )
                    )
                )
            )
        );

        putenv('DBx_HOST=db1host');
        putenv('DBx_PORT=db1port');

        $this->process($configs);
    }

    public function testConnectionMapBadId1()
    {
        $this->setExpectedException(

            'Symfony\Component\Config\Definition\Exception\InvalidConfigurationException',
            'Invalid configuration for path "pagoda_box.doctrine.dbal.connections.other": "DB3" is not a valid Pagoda Box database identifier. Should be DB1, DB2, etc.'
        );

        $configs = array(
            array(
                Configuration::KEY_DOCTRINE => array(
                    Configuration::KEY_DOCTRINE_DBAL => array(
                        'connections' => array(

                            'default' => 'DB1',
                            'other'   => 'DB3',
                        )
                    )
                )
            )
        );

        putenv('DB1_HOST=db1host');
        putenv('DB1_PORT=db1port');

        $this->process($configs);
    }

    public function testConnectionMapGood()
    {
        $configs = array(
            array(
                Configuration::KEY_DOCTRINE => array(
                    Configuration::KEY_DOCTRINE_DBAL => array(
                        'connections' => array(

                            'default' => 'DB1',
                            'other'   => 'DB3',
                        )
                    )
                )
            )
        );

        putenv('DB1_HOST=db1host');
        putenv('DB1_PORT=db1port');
        putenv('DB3_HOST=db3host');
        putenv('DB3_PORT=db3port');

        $processed = $this->process($configs);

        $map = $processed[Configuration::KEY_DOCTRINE][Configuration::KEY_DOCTRINE_DBAL][Configuration::KEY_DOCTRINE_DBAL_CONNECTIONS];

        $this->assertTrue(is_array($map));

        $expected = array(

            'default' => 'DB1',
            'other'   => 'DB3'
        );

        $this->assertEquals($expected, $map);
    }

    public function testDoctrineDbalEmptyConnections()
    {
        $configs = array(
            array(
                Configuration::KEY_DOCTRINE => array(
                    Configuration::KEY_DOCTRINE_DBAL => array(
                        'connections' => array(

                        )
                    )
                )
            )
        );

        $processed = $this->process($configs);

        $this->assertTrue(is_array($processed[Configuration::KEY_DOCTRINE][Configuration::KEY_DOCTRINE_DBAL][Configuration::KEY_DOCTRINE_DBAL_CONNECTIONS]));
    }

    public function testDoctrineDbalConnectionsOnly()
    {
        $this->setExpectedException(

            'Symfony\Component\Config\Definition\Exception\InvalidConfigurationException',
            'Unrecognized options "0" under "pagoda_box.doctrine.dbal"'
        );

        $configs = array(
            array(
                Configuration::KEY_DOCTRINE => array(
                    Configuration::KEY_DOCTRINE_DBAL => array(
                        'connections'
                    )
                )
            )
        );

        $this->process($configs);
    }

    public function testDoctrineOrmOnly()
    {
        $this->setExpectedException(

            'Symfony\Component\Config\Definition\Exception\InvalidConfigurationException',
            'Unrecognized options "0" under "pagoda_box.doctrine"'
        );

        $configs = array(
            array(
                Configuration::KEY_DOCTRINE => array(
                    Configuration::KEY_DOCTRINE_ORM
                )
            )
        );

        $this->process($configs);
    }

    public function testDoctrineDbalOnly()
    {
        $this->setExpectedException(

            'Symfony\Component\Config\Definition\Exception\InvalidConfigurationException',
            'Unrecognized options "0" under "pagoda_box.doctrine"'
        );

        $configs = array(
            array(
                Configuration::KEY_DOCTRINE => array(
                    Configuration::KEY_DOCTRINE_DBAL
                )
            )
        );

        $this->process($configs);
    }

    public function testDoctrineOnly()
    {
        $this->setExpectedException(

            'Symfony\Component\Config\Definition\Exception\InvalidConfigurationException',
            'Unrecognized options "0" under "pagoda_box"'
        );

        $configs = array(
            array(
                Configuration::KEY_DOCTRINE
            )
        );

        $this->process($configs);
    }

    public function testRedisSessionsGoodMissing()
    {
        $configs = array();

        $config = $this->process($configs);

        $this->assertArrayHasKey(Configuration::KEY_STORE_SESSIONS_IN_REDIS, $config);
        $this->assertFalse($config[Configuration::KEY_STORE_SESSIONS_IN_REDIS]);
    }

    public function testRedisSessionsGoodFalse()
    {
        $configs = array(
            array(
                Configuration::KEY_STORE_SESSIONS_IN_REDIS => false
            )
        );

        $config = $this->process($configs);

        $this->assertArrayHasKey(Configuration::KEY_STORE_SESSIONS_IN_REDIS, $config);
        $this->assertFalse($config[Configuration::KEY_STORE_SESSIONS_IN_REDIS]);
    }

    public function testRedisSessionsGoodTrue()
    {
        $configs = array(
            array(
                Configuration::KEY_STORE_SESSIONS_IN_REDIS => true
            )
        );

        $config = $this->process($configs);

        $this->assertArrayHasKey(Configuration::KEY_STORE_SESSIONS_IN_REDIS, $config);
        $this->assertTrue($config[Configuration::KEY_STORE_SESSIONS_IN_REDIS]);
    }

    public function testAnnotationsCacheBadIdSyntax()
    {
        $this->setExpectedException(

            'Symfony\Component\Config\Definition\Exception\InvalidConfigurationException',
            'Invalid configuration for path "pagoda_box.annotations_cache.type": "xyz" is not a valid annotation cache type. Must be "memcache", "memcached", or "redis". The corresponding PHP extension must also be loaded, along with the corresponding Doctrine cache class.'
        );

        $configs = array(
            array(
                Configuration::KEY_ANNOTATIONS_CACHE => array(

                    Configuration::KEY_CACHE_TYPE => 'xyz',
                    Configuration::KEY_CACHE_ID => 'CACHExy'
                )
            )
        );

        putenv('CACHExy_HOST=some host');
        putenv('CACHExy_PORT=12');

        $this->process($configs);
    }

    public function testAnnotationsCacheBadIdNoEnv()
    {
        $this->setExpectedException(

            'Symfony\Component\Config\Definition\Exception\InvalidConfigurationException',
            'Invalid configuration for path "pagoda_box.annotations_cache.pagoda_env_id": "CACHE12" is not a valid Pagoda Box cache identifier. Should be CACHE1, CACHE2, etc., and the corresponding Pagoda Box environment variables must be present.'
        );

        $configs = array(
            array(
                Configuration::KEY_ANNOTATIONS_CACHE => array(

                    Configuration::KEY_CACHE_TYPE => Configuration::CACHE_TYPE_MEMCACHED,
                    Configuration::KEY_CACHE_ID   => 'CACHE12'
                )
            )
        );

        $this->process($configs);
    }

    public function testAnnotationsCacheBadType()
    {
        $this->setExpectedException(

            'Symfony\Component\Config\Definition\Exception\InvalidConfigurationException',
            'Invalid configuration for path "pagoda_box.annotations_cache.type": "xyz" is not a valid annotation cache type. Must be "memcache", "memcached", or "redis". The corresponding PHP extension must also be loaded, along with the corresponding Doctrine cache class.'
        );

        $configs = array(
            array(
                Configuration::KEY_ANNOTATIONS_CACHE => array(

                    Configuration::KEY_CACHE_TYPE => 'xyz',
                    Configuration::KEY_CACHE_ID => 'CACHE12'
                )
            )
        );

        putenv('CACHE12_HOST=some host');
        putenv('CACHE12_PORT=12');

        $this->process($configs);
    }

    public function testAnnotationsCacheGood()
    {
        $configs = array(
            array(
                Configuration::KEY_ANNOTATIONS_CACHE => array(
                    
                    Configuration::KEY_CACHE_TYPE => Configuration::CACHE_TYPE_MEMCACHED,
                    Configuration::KEY_CACHE_ID => 'CACHE12'
                )
            )
        );

        putenv('CACHE12_HOST=some host');
        putenv('CACHE12_PORT=12');

        $config = $this->process($configs);

        $this->assertArrayHasKey(Configuration::KEY_ANNOTATIONS_CACHE, $config);
        $this->assertTrue(is_array($config[Configuration::KEY_ANNOTATIONS_CACHE]));
        $this->assertArrayHasKey(Configuration::KEY_CACHE_ID, $config[Configuration::KEY_ANNOTATIONS_CACHE]);
        $this->assertEquals('CACHE12', $config[Configuration::KEY_ANNOTATIONS_CACHE][Configuration::KEY_CACHE_ID]);
        $this->assertEquals(Configuration::CACHE_TYPE_MEMCACHED, $config[Configuration::KEY_ANNOTATIONS_CACHE][Configuration::KEY_CACHE_TYPE]);
    }

    /**
     * Processes an array of configurations and returns a compiled version.
     *
     * @param array $configs An array of raw configurations
     *
     * @return array A normalized array
     */
    protected function process($configs)
    {
        $processor = new Processor();

        return $processor->processConfiguration(new Configuration(), $configs);
    }

    public function tearDown()
    {
        putenv('CACHE12_HOST');
        putenv('CACHE12_PORT');
        putenv('CACHExy_HOST');
        putenv('CACHExy_PORT');
        putenv('DB1_HOST');
        putenv('DB1_PORT');
        putenv('DB3_HOST');
        putenv('DB3_PORT');
        putenv('DBx=db1host');
        putenv('DBx=db1port');
    }
}