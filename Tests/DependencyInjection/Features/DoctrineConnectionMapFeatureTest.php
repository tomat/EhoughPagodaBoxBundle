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
use Ehough\Bundle\PagodaBoxBundle\DependencyInjection\Features\RedisSessionHandlerFeature;

/**
 * @covers \Ehough\Bundle\PagodaBoxBundle\DependencyInjection\Features\DoctrineConnectionMapFeature<extended>
 */
class DoctrineConnectionMapFeatureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DoctrineConnectionMapFeature
     */
    private $_sut;

    /**
     * @var \Mockery\MockInterface
     */
    private $_mockContainer;

    public function setUp()
    {
        $this->_sut           = new DoctrineConnectionMapFeature();
        $this->_mockContainer = \Mockery::mock('\Symfony\Component\DependencyInjection\ContainerBuilder');
    }

    public function testActNoMap()
    {
        $config = array(
            Configuration::KEY_DOCTRINE => array(
                Configuration::KEY_DOCTRINE_DBAL => array(
                    Configuration::KEY_DOCTRINE_DBAL_CONNECTIONS => array(
                        'default' => 'DB1',
                    )
                )
            )
        );

        putenv('DB1_HOST=some host');
        putenv('DB1_PORT=111');
        putenv('DB1_USER=some user');
        putenv('DB1_NAME=some name');
        putenv('DB1_PASS=some pass');

        $map = array('default' => array(

            DoctrineConnectionMapFeature::CONNECTION_KEY_HOST => 'some host',
            DoctrineConnectionMapFeature::CONNECTION_KEY_PORT => 111,
            DoctrineConnectionMapFeature::CONNECTION_KEY_USER => 'some user',
            DoctrineConnectionMapFeature::CONNECTION_KEY_PASS => 'some pass',
            DoctrineConnectionMapFeature::CONNECTION_KEY_NAME => 'some name',
        ));

        $this->_mockContainer->shouldReceive('setParameter')->once()->with(DoctrineConnectionMapFeature::CONNECTION_MAP_ID, $map);

        $this->_sut->act($config, $this->_mockContainer);
    }

    public function testShouldAct()
    {
        $config = array(
            Configuration::KEY_DOCTRINE => array(
                Configuration::KEY_DOCTRINE_DBAL => array(
                    Configuration::KEY_DOCTRINE_DBAL_CONNECTIONS => array(

                    )
                )
            )
        );

        $this->assertTrue($this->_sut->shouldAct($config, $this->_mockContainer));
    }

    public function testShouldActNoConnections()
    {
        $config = array(
            Configuration::KEY_DOCTRINE => array(
                Configuration::KEY_DOCTRINE_DBAL => array(

                )
            )
        );

        $this->assertFalse($this->_sut->shouldAct($config, $this->_mockContainer));
    }

    public function testShouldActNoDbal()
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
        putenv('DB1_HOST');
        putenv('DB1_PORT');
        putenv('DB1_USER');
        putenv('DB1_NAME');
        putenv('DB1_PASS');

        \Mockery::close();
    }
}