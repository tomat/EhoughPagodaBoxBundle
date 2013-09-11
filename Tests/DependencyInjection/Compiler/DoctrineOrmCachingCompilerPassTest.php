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

namespace Ehough\Bundle\PagodaBoxBundle\Tests\DependencyInjection\CompilerPass;

use Ehough\Bundle\PagodaBoxBundle\DependencyInjection\Compiler\DoctrineConnectionMappingCompilerPass;
use Ehough\Bundle\PagodaBoxBundle\DependencyInjection\Compiler\DoctrineOrmCachingCompilerPass;
use Ehough\Bundle\PagodaBoxBundle\DependencyInjection\Configuration;
use Ehough\Bundle\PagodaBoxBundle\DependencyInjection\Features\DoctrineConnectionMapFeature;
use Ehough\Bundle\PagodaBoxBundle\DependencyInjection\Features\DoctrineOrmCacheFeature;
use Symfony\Component\DependencyInjection\Alias;

/**
 * @covers \Ehough\Bundle\PagodaBoxBundle\DependencyInjection\Compiler\DoctrineOrmCachingCompilerPass
 */
class DoctrineOrmCachingCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DoctrineOrmCachingCompilerPass
     */
    private $_sut;

    /**
     * @var \Mockery\MockInterface
     */
    private $_mockContainer;

    public function setUp()
    {
        $this->_sut           = new DoctrineOrmCachingCompilerPass();
        $this->_mockContainer = \Mockery::mock('\Symfony\Component\DependencyInjection\ContainerBuilder');
    }

    public function testProcess()
    {
        $map = array(
            'default' => array(
                Configuration::KEY_DOCTRINE_ORM_CACHETYPE_METADATA => 'metadata_cache_1',  //no
                Configuration::KEY_DOCTRINE_ORM_CACHETYPE_QUERY => 'query_cache1',         //no
            ),
            'other' => array(
                Configuration::KEY_DOCTRINE_ORM_CACHETYPE_RESULT => 'result_cache_2',
                Configuration::KEY_DOCTRINE_ORM_CACHETYPE_METADATA => 'metadata_cache_2'
            )
        );

        $mockOtherResultCache   = \Mockery::mock('\Symfony\Component\DependencyInjection\Definition');
        $mockOtherMetadataCache = \Mockery::mock('\Symfony\Component\DependencyInjection\Definition');
        $mockResultCache2       = \Mockery::mock('\Symfony\Component\DependencyInjection\Definition');
        $mockMetadataCache2     = \Mockery::mock('\Symfony\Component\DependencyInjection\Definition');

        $this->_mockContainer->shouldReceive('hasParameter')->once()->with(DoctrineOrmCacheFeature::CACHE_MAP_ID)->andReturn(true);
        $this->_mockContainer->shouldReceive('getParameter')->once()->with(DoctrineOrmCacheFeature::CACHE_MAP_ID)->andReturn($map);

        $this->_mockContainer->shouldReceive('hasDefinition')->once()->with('doctrine.orm.default_metadata_cache')->andReturn(false);
        $this->_mockContainer->shouldReceive('hasDefinition')->once()->with('doctrine.orm.default_query_cache')->andReturn(true);
        $this->_mockContainer->shouldReceive('hasDefinition')->once()->with('query_cache1')->andReturn(false);
        $this->_mockContainer->shouldReceive('hasDefinition')->once()->with('doctrine.orm.other_result_cache')->andReturn(true);
        $this->_mockContainer->shouldReceive('hasDefinition')->once()->with('result_cache_2')->andReturn(true);
        $this->_mockContainer->shouldReceive('hasDefinition')->once()->with('doctrine.orm.other_metadata_cache')->andReturn(true);
        $this->_mockContainer->shouldReceive('hasDefinition')->once()->with('metadata_cache_2')->andReturn(true);

        $this->_mockContainer->shouldReceive('getDefinition')->once()->with('result_cache_2')->andReturn($mockResultCache2);
        $this->_mockContainer->shouldReceive('getDefinition')->once()->with('metadata_cache_2')->andReturn($mockMetadataCache2);

        $this->_mockContainer->shouldReceive('setDefinition')->once()->with('doctrine.orm.other_result_cache', $mockResultCache2);
        $this->_mockContainer->shouldReceive('setDefinition')->once()->with('doctrine.orm.other_metadata_cache', $mockMetadataCache2);

        $this->_sut->process($this->_mockContainer);
    }

    public function testProcessNoMap()
    {
        $this->_mockContainer->shouldReceive('hasParameter')->once()->with(DoctrineOrmCacheFeature::CACHE_MAP_ID)->andReturn(false);

        $this->_sut->process($this->_mockContainer);
    }

    public function tearDown()
    {
        \Mockery::close();
    }
}