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

use Ehough\Bundle\PagodaBoxBundle\DependencyInjection\Compiler\AliasSwappingCompilerPass;
use Ehough\Bundle\PagodaBoxBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\Alias;

/**
 * @covers \Ehough\Bundle\PagodaBoxBundle\DependencyInjection\Compiler\AliasSwappingCompilerPass
 */
class AliasSwappingCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AliasSwappingCompilerPass
     */
    private $_sut;

    /**
     * @var \Mockery\MockInterface
     */
    private $_mockContainer;

    public function setUp()
    {
        $this->_sut           = new AliasSwappingCompilerPass('alias', 'service');
        $this->_mockContainer = \Mockery::mock('\Symfony\Component\DependencyInjection\ContainerBuilder');
    }

    public function testConstructNonStringService()
    {
        $this->setExpectedException(

            'InvalidArgumentException',
            'New service ID must be a string'
        );

        new AliasSwappingCompilerPass('string', array());
    }

    public function testConstructNonStringAlias()
    {
        $this->setExpectedException(

            'InvalidArgumentException',
            'Original alias ID must be a string'
        );

        new AliasSwappingCompilerPass(array(), 'string');
    }

    public function testProcess()
    {
        $this->_mockContainer->shouldReceive('hasAlias')->once()->with('alias')->andReturn(true);
        $this->_mockContainer->shouldReceive('hasDefinition')->once()->with('service')->andReturn(true);

        $this->_mockContainer->shouldReceive('setAlias')->once()->with('alias', \Mockery::on(function ($alias) {

            return $alias instanceof Alias && "$alias" === 'service';
        }));

        $this->_sut->process($this->_mockContainer);
    }

    public function testProcessNoAlias()
    {
        $this->_mockContainer->shouldReceive('hasAlias')->once()->with('alias')->andReturn(false);

        $this->_sut->process($this->_mockContainer);
    }

    public function testProcessNoDefinition()
    {
        $this->_mockContainer->shouldReceive('hasAlias')->once()->with('alias')->andReturn(true);
        $this->_mockContainer->shouldReceive('hasDefinition')->once()->with('service')->andReturn(false);

        $this->_sut->process($this->_mockContainer);
    }

    public function tearDown()
    {
        \Mockery::close();
    }
}