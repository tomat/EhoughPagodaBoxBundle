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
use Ehough\Bundle\PagodaBoxBundle\DependencyInjection\Features\RedisSessionHandlerFeature;

/**
 * @covers \Ehough\Bundle\PagodaBoxBundle\DependencyInjection\Features\RedisSessionHandlerFeature
 */
class RedisSessionHandlerFeatureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RedisSessionHandlerFeature
     */
    private $_sut;

    /**
     * @var \Mockery\MockInterface
     */
    private $_mockContainer;

    public function setUp()
    {
        $this->_sut           = new RedisSessionHandlerFeature();
        $this->_mockContainer = \Mockery::mock('\Symfony\Component\DependencyInjection\ContainerBuilder');
    }

    public function testBadSessionPath()
    {
        $old = ini_set('session.save_path', 'tcp://something.com:379');

        $this->assertFalse($this->_sut->__isSessionPathSetupCorrectly());

        ini_set('session.save_path', $old);
    }

    public function testGoodSessionPath()
    {
        $old = ini_set('session.save_path', 'tcp://something.com:6379');

        $this->assertTrue($this->_sut->__isSessionPathSetupCorrectly());

        ini_set('session.save_path', $old);
    }

    public function testExtensionLoaded()
    {
        $this->assertFalse($this->_sut->__isRedisExtensionLoaded());
    }

    public function testSessionHandler()
    {
        $this->assertFalse($this->_sut->__isSessionHandlerSetupCorrectly());
    }

    public function testAct()
    {
        $config = array();

        $this->_mockContainer->shouldReceive('register')->once()->with(
            RedisSessionHandlerFeature::SERVICE_ID_SESSION_HANDLER,
            'Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeSessionHandler');

        $this->_sut->act($config, $this->_mockContainer);
    }

    public function testShouldActExplicitFalse()
    {
        $config = array(
            Configuration::KEY_STORE_SESSIONS_IN_REDIS => false
        );

        $this->assertFalse($this->_sut->shouldAct($config, $this->_mockContainer));
    }

    public function testShouldActCompletelyOmit()
    {
        $config = array();

        $this->assertFalse($this->_sut->shouldAct($config, $this->_mockContainer));
    }

    public function tearDown()
    {
        \Mockery::close();
    }
}