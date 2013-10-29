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

use Ehough\Bundle\PagodaBoxBundle\DependencyInjection\Compiler\DoctrineConnectionMappingCompilerPass;
use Ehough\Bundle\PagodaBoxBundle\DependencyInjection\Compiler\DoctrineOrmCachingCompilerPass;
use Ehough\Bundle\PagodaBoxBundle\DependencyInjection\Features\AnnotationsCacheFeature;
use Ehough\Bundle\PagodaBoxBundle\DependencyInjection\Features\DoctrineConnectionMapFeature;
use Ehough\Bundle\PagodaBoxBundle\DependencyInjection\Features\DoctrineOrmCacheFeature;
use Ehough\Bundle\PagodaBoxBundle\DependencyInjection\Features\RedisSessionHandlerFeature;
use Ehough\Bundle\PagodaBoxBundle\EhoughPagodaBoxBundle;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class EhoughPagodaBoxExtension extends Extension
{
    /**
     * @var \Ehough\Bundle\PagodaBoxBundle\DependencyInjection\Features\FeatureInterface[]
     */
    private $_features;

    public function __construct()
    {
        $this->_features = array(

            new RedisSessionHandlerFeature(),
            new AnnotationsCacheFeature(),
            new DoctrineConnectionMapFeature(),
            new DoctrineOrmCacheFeature()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function load(array $rawConfiguration, ContainerBuilder $container)
    {
        if (!count($rawConfiguration[0])) {

            return;
        }

        $configuration = new Configuration();

        $processedConfiguration = $this->processConfiguration($configuration, $rawConfiguration);

        foreach ($this->_features as $feature) {

            if ($feature->shouldAct($processedConfiguration, $container)) {

                $feature->act($processedConfiguration, $container);
            }
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('services.yml');
    }
}
