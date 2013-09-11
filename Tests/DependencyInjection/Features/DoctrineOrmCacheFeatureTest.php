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

namespace Ehough\Bundle\PagodaBoxBundle\Tests\DependencyInjection\Features;

use Ehough\Bundle\PagodaBoxBundle\DependencyInjection\Configuration;
use Ehough\Bundle\PagodaBoxBundle\DependencyInjection\Features\DoctrineConnectionMapFeature;
use Ehough\Bundle\PagodaBoxBundle\DependencyInjection\Features\DoctrineOrmCacheFeature;
use Ehough\Bundle\PagodaBoxBundle\DependencyInjection\Features\RedisSessionHandlerFeature;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @covers \Ehough\Bundle\PagodaBoxBundle\DependencyInjection\Features\DoctrineOrmCacheFeature<extended>
 */
class DoctrineOrmCacheFeatureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DoctrineOrmCacheFeature
     */
    private $_sut;

    /**
     * @var \Mockery\MockInterface
     */
    private $_mockContainer;

    public function setUp()
    {
        $this->_sut           = new DoctrineOrmCacheFeature();
        $this->_mockContainer = \Mockery::mock('\Symfony\Component\DependencyInjection\ContainerBuilder');
    }

    public function testAct()
    {
        $config = array(
            Configuration::KEY_DOCTRINE => array(
                Configuration::KEY_DOCTRINE_ORM => array(
                    Configuration::KEY_DOCTRINE_ORM_CACHING => array(
                        'default' => array(

                            'query' => array(
                                Configuration::KEY_CACHE_ID => 'CACHE1',
                                Configuration::KEY_CACHE_TYPE => Configuration::CACHE_TYPE_MEMCACHED
                            ),
                            'result' => array(
                                Configuration::KEY_CACHE_ID => 'CACHE2',
                                Configuration::KEY_CACHE_TYPE => Configuration::CACHE_TYPE_REDIS
                            )
                        ),
                        'other' => array(

                            'query' => array(
                                Configuration::KEY_CACHE_ID => 'CACHE3',
                                Configuration::KEY_CACHE_TYPE => Configuration::CACHE_TYPE_MEMCACHE
                            ),
                            'badname' => array(
                                Configuration::KEY_CACHE_ID => 'CACHE3',
                                Configuration::KEY_CACHE_TYPE => Configuration::CACHE_TYPE_MEMCACHE
                            ),
                        )
                    )
                )
            )
        );

        putenv('CACHE1_HOST=111.111.111.111');
        putenv('CACHE1_PORT=111');
        putenv('CACHE2_HOST=222.222.222.222');
        putenv('CACHE2_PORT=222');
        putenv('CACHE3_HOST=123.123.123.123');
        putenv('CACHE3_PORT=333');

        $this->_mockContainer->shouldReceive('setDefinition')->once()->with(\Mockery::on(function ($serverId) {

            return preg_match_all('/^ehough_pagoda_box\.memcached_instance_[0-9]+$/', $serverId, $matches) === 1;

        }), \Mockery::on(function ($definition) {

            return $definition instanceof Definition && $definition->getClass() === 'Memcached'
                && $definition->getArgument(0) === 'ehough_pagoda_box.doctrine_default_query_cache'
                && $definition->getMethodCalls() == array(array('addServer', array('111.111.111.111', 111)));
        }));

        $this->_mockContainer->shouldReceive('setDefinition')->once()->with(\Mockery::on(function ($serverId) {

            return preg_match_all('/^ehough_pagoda_box\.doctrine_default_query_cache_[0-9]+$/', $serverId, $matches) === 1;

        }), \Mockery::on(function ($definition) {

            return $definition instanceof Definition && $definition->getClass() === 'Doctrine\Common\Cache\MemcachedCache';
        }));

        $this->_mockContainer->shouldReceive('setDefinition')->once()->with(\Mockery::on(function ($serverId) {

            return preg_match_all('/^ehough_pagoda_box\.redis_instance_[0-9]+$/', $serverId, $matches) === 1;

        }), \Mockery::on(function ($definition) {

            return $definition instanceof Definition && $definition->getClass() === 'Redis'
            && $definition->getMethodCalls() == array(array('pconnect', array('222.222.222.222', 222, 30, 'ehough_pagoda_box.doctrine_default_result_cache')));
        }));

        $this->_mockContainer->shouldReceive('setDefinition')->once()->with(\Mockery::on(function ($serverId) {

            return preg_match_all('/^ehough_pagoda_box\.doctrine_default_result_cache_[0-9]+$/', $serverId, $matches) === 1;

        }), \Mockery::on(function ($definition) {

            return $definition instanceof Definition && $definition->getClass() === 'Doctrine\Common\Cache\RedisCache';
        }));

        $this->_mockContainer->shouldReceive('setDefinition')->once()->with(\Mockery::on(function ($serverId) {

            return preg_match_all('/^ehough_pagoda_box\.memcache_instance_[0-9]+$/', $serverId, $matches) === 1;

        }), \Mockery::on(function ($definition) {

            return $definition instanceof Definition && $definition->getClass() === 'Memcache'
            && $definition->getMethodCalls() == array(array('addServer', array('123.123.123.123', 333)));
        }));

        $this->_mockContainer->shouldReceive('setDefinition')->once()->with(\Mockery::on(function ($serverId) {

            return preg_match_all('/^ehough_pagoda_box\.doctrine_other_query_cache_[0-9]+$/', $serverId, $matches) === 1;

        }), \Mockery::on(function ($definition) {

            return $definition instanceof Definition && $definition->getClass() === 'Doctrine\Common\Cache\MemcacheCache';
        }));

        $this->_mockContainer->shouldReceive('setParameter')->once()->with(DoctrineOrmCacheFeature::CACHE_MAP_ID, \Mockery::on(function ($map) {

            return is_array($map)
                && preg_match_all('/^ehough_pagoda_box\.doctrine_default_query_cache_[0-9]+$/', $map['default']['query'], $matches)
                && preg_match_all('/^ehough_pagoda_box\.doctrine_default_result_cache_[0-9]+$/', $map['default']['result'], $matches)
                && preg_match_all('/^ehough_pagoda_box\.doctrine_other_query_cache_[0-9]+$/', $map['other']['query'], $matches);
        }));

        $this->_sut->act($config, $this->_mockContainer);
    }

    public function testShouldAct()
    {
        $config = array(
            Configuration::KEY_DOCTRINE => array(
                Configuration::KEY_DOCTRINE_ORM => array(
                    Configuration::KEY_DOCTRINE_ORM_CACHING => array(

                    )
                )
            )
        );

        $this->assertTrue($this->_sut->shouldAct($config, $this->_mockContainer));
    }

    public function testShouldActNoCache()
    {
        $config = array(
            Configuration::KEY_DOCTRINE => array(
                Configuration::KEY_DOCTRINE_ORM => array(

                )
            )
        );

        $this->assertFalse($this->_sut->shouldAct($config, $this->_mockContainer));
    }

    public function testShouldActNoOrm()
    {
        $config = array(
            Configuration::KEY_DOCTRINE => array(

            )
        );

        $this->assertFalse($this->_sut->shouldAct($config, $this->_mockContainer));
    }

    public function testShouldActOmit()
    {
        $config = array();

        $this->assertFalse($this->_sut->shouldAct($config, $this->_mockContainer));
    }

    public function tearDown()
    {
        putenv('CACHE1_HOST');
        putenv('CACHE1_PORT');
        putenv('CACHE2_HOST');
        putenv('CACHE2_PORT');
        putenv('CACHE3_HOST');
        putenv('CACHE3_PORT');

        \Mockery::close();
    }
}