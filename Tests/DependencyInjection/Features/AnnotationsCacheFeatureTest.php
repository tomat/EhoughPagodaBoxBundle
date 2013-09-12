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
use Ehough\Bundle\PagodaBoxBundle\DependencyInjection\Features\AnnotationsCacheFeature;
use Ehough\Bundle\PagodaBoxBundle\DependencyInjection\Features\DoctrineConnectionMapFeature;
use Ehough\Bundle\PagodaBoxBundle\DependencyInjection\Features\DoctrineOrmCacheFeature;
use Ehough\Bundle\PagodaBoxBundle\DependencyInjection\Features\RedisSessionHandlerFeature;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @covers \Ehough\Bundle\PagodaBoxBundle\DependencyInjection\Features\AnnotationsCacheFeature<extended>
 */
class AnnotationsCacheFeatureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AnnotationsCacheFeature
     */
    private $_sut;

    /**
     * @var \Mockery\MockInterface
     */
    private $_mockContainer;

    public function setUp()
    {
        $this->_sut           = new AnnotationsCacheFeature();
        $this->_mockContainer = \Mockery::mock('\Symfony\Component\DependencyInjection\ContainerBuilder');
    }

    public function testAct()
    {
        $config = array(
            Configuration::KEY_ANNOTATIONS_CACHE => array(
                Configuration::KEY_CACHE_TYPE => Configuration::CACHE_TYPE_MEMCACHED,
                Configuration::KEY_CACHE_ID => 'CACHE1'
            )
        );

        putenv('CACHE1_HOST=111.111.111.111');
        putenv('CACHE1_PORT=111');

        $this->_mockContainer->shouldReceive('getParameter')->once()->with('kernel.debug')->andReturn(true);

        $this->_mockContainer->shouldReceive('setDefinition')->once()->with(\Mockery::on(function ($serverId) {

            return preg_match_all('/^ehough_pagoda_box\.memcached_instance_[0-9]+$/', $serverId, $matches) === 1;

        }), \Mockery::on(function ($definition) {

            return $definition instanceof Definition && $definition->getClass() === 'Memcached'
            && $definition->getArgument(0) === 'ehough_pagoda_box.annotations_cache'
            && $definition->getMethodCalls() == array(array('addServer', array('111.111.111.111', 111)));
        }));

        $this->_mockContainer->shouldReceive('setDefinition')->once()->with('ehough_pagoda_box.annotations_reader_cache', \Mockery::on(function ($definition) {

            return $definition instanceof Definition && $definition->getClass() === 'Doctrine\Common\Cache\MemcachedCache';
        }));

        $this->_mockContainer->shouldReceive('register')->once()->with(

            AnnotationsCacheFeature::SERVICE_ID_ANNOTATION_READER,
            'Doctrine\Common\Annotations\CachedReader'
        )->andReturn($this->_mockContainer);

        $this->_mockContainer->shouldReceive('addArgument')->once()->with(\Mockery::on(function ($reference) {

            return $reference instanceof Reference && "$reference" === AnnotationsCacheFeature::ALIAS_ID_ORIGINAL_ANNOTATIONS_READER;
        }))->andReturn($this->_mockContainer);

        $this->_mockContainer->shouldReceive('addArgument')->once()->with(\Mockery::on(function ($reference) {

            return $reference instanceof Reference && "$reference" === 'ehough_pagoda_box.annotations_reader_cache';
        }))->andReturn($this->_mockContainer);

        $this->_mockContainer->shouldReceive('addArgument')->once()->with(true);

        $this->_sut->act($config, $this->_mockContainer);
    }

    public function testShouldAct()
    {
        $config = array(
            Configuration::KEY_ANNOTATIONS_CACHE => array(
                Configuration::KEY_CACHE_TYPE => Configuration::CACHE_TYPE_MEMCACHED,
                Configuration::KEY_CACHE_ID => 'CACHE2'
            )
        );

        $this->assertTrue($this->_sut->shouldAct($config, $this->_mockContainer));
    }

    public function testActMissingId()
    {
        $config = array(
            Configuration::KEY_ANNOTATIONS_CACHE => array(
                Configuration::KEY_CACHE_TYPE => Configuration::CACHE_TYPE_MEMCACHED
            )
        );

        $this->assertFalse($this->_sut->shouldAct($config, $this->_mockContainer));
    }

    public function testActMissingType()
    {
        $config = array(
            Configuration::KEY_ANNOTATIONS_CACHE => array(
                Configuration::KEY_CACHE_ID => 'CACHE2'
            )
        );

        $this->assertFalse($this->_sut->shouldAct($config, $this->_mockContainer));
    }

    public function testShouldActOmit3()
    {
        $config = array(
            Configuration::KEY_ANNOTATIONS_CACHE => array(

            )
        );

        $this->assertFalse($this->_sut->shouldAct($config, $this->_mockContainer));
    }

    public function testShouldActOmit2()
    {
        $config = array(
            Configuration::KEY_ANNOTATIONS_CACHE => 'xyz'
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

        \Mockery::close();
    }
}