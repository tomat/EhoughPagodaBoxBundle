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
use Ehough\Bundle\PagodaBoxBundle\DependencyInjection\Configuration;
use Ehough\Bundle\PagodaBoxBundle\DependencyInjection\Features\DoctrineConnectionMapFeature;
use Symfony\Component\DependencyInjection\Alias;

/**
 * @covers \Ehough\Bundle\PagodaBoxBundle\DependencyInjection\Compiler\DoctrineConnectionMappingCompilerPass
 */
class DoctrineConnectionMappingCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DoctrineConnectionMappingCompilerPass
     */
    private $_sut;

    /**
     * @var \Mockery\MockInterface
     */
    private $_mockContainer;

    public function setUp()
    {
        $this->_sut           = new DoctrineConnectionMappingCompilerPass();
        $this->_mockContainer = \Mockery::mock('\Symfony\Component\DependencyInjection\ContainerBuilder');
    }

    public function testProcess()
    {
        $map = array(

            'default' => array(

                DoctrineConnectionMapFeature::CONNECTION_KEY_HOST => 'some host',
                DoctrineConnectionMapFeature::CONNECTION_KEY_PORT => 12,
                DoctrineConnectionMapFeature::CONNECTION_KEY_USER => 'some user',
                DoctrineConnectionMapFeature::CONNECTION_KEY_PASS => 'some pass',
                DoctrineConnectionMapFeature::CONNECTION_KEY_NAME => 'some name',
            ),

            'other' => array(

                DoctrineConnectionMapFeature::CONNECTION_KEY_HOST => 'some host1',
                DoctrineConnectionMapFeature::CONNECTION_KEY_PORT => 121,
                DoctrineConnectionMapFeature::CONNECTION_KEY_USER => 'some user1',
                DoctrineConnectionMapFeature::CONNECTION_KEY_PASS => 'some pass1',
                DoctrineConnectionMapFeature::CONNECTION_KEY_NAME => 'some name1',
            ),
        );

        $mockConnectionDef = \Mockery::mock('\Symfony\Component\DependencyInjection\Definition');
        $mockConnectionDef->shouldReceive('getArgument')->once()->with(0)->andReturn(array());
        $mockConnectionDef->shouldReceive('replaceArgument')->once()->with(0, \Mockery::on(function ($newParams) {

            return $newParams == array(

                'dbname' => 'some name1',
                'host' => 'some host1',
                'port' => 121,
                'password' => 'some pass1',
                'user' => 'some user1',
            );
        }));

        $this->_mockContainer->shouldReceive('hasParameter')->once()->with(DoctrineConnectionMapFeature::CONNECTION_MAP_ID)->andReturn(true);
        $this->_mockContainer->shouldReceive('getParameter')->once()->with(DoctrineConnectionMapFeature::CONNECTION_MAP_ID)->andReturn($map);
        $this->_mockContainer->shouldReceive('hasDefinition')->once()->with('doctrine.dbal.default_connection')->andReturn(false);
        $this->_mockContainer->shouldReceive('hasDefinition')->once()->with('doctrine.dbal.other_connection')->andReturn(true);
        $this->_mockContainer->shouldReceive('getDefinition')->once()->with('doctrine.dbal.other_connection')->andReturn($mockConnectionDef);


        $this->_sut->process($this->_mockContainer);
    }

    public function testProcessNoMap()
    {
        $this->_mockContainer->shouldReceive('hasParameter')->once()->with(DoctrineConnectionMapFeature::CONNECTION_MAP_ID)->andReturn(false);

        $this->_sut->process($this->_mockContainer);
    }

    public function tearDown()
    {
        \Mockery::close();
    }
}