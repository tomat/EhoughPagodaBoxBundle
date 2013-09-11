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

namespace Ehough\Bundle\PagodaBoxBundle\DependencyInjection\Features;

use Ehough\Bundle\PagodaBoxBundle\DependencyInjection\Configuration;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 *
 */
class AnnotationsCacheFeature extends AbstractDoctrineCacheBasedFeature
{
    const SERVICE_ID_ANNOTATIONS_READER        = 'ehough_pagoda_box.annotations_reader';
    const ALIAS_ID_ORIGINAL_ANNOTATIONS_READER = 'annotations.reader';

    private static $_SERVICE_ID_ANNOTATIONS_READER_CACHE = 'ehough_pagoda_box.annotations_reader_cache';

    /**
     * @param array            $processedConfiguration The processed and validated configuration for the bundle.
     * @param ContainerBuilder $container              The container.
     *
     * @return boolean True if the feature can and should act on the given configuration.
     */
    public function shouldAct(array $processedConfiguration, ContainerBuilder $container)
    {
        if (!isset($processedConfiguration[Configuration::KEY_ANNOTATIONS_CACHE])) {

            //they didn't set the annotations cache
            return false;
        }

        if (!is_array($processedConfiguration[Configuration::KEY_ANNOTATIONS_CACHE])) {

            //it's not an array, this will never happen..
            return false;
        }

        $annotationsCacheSettings = $processedConfiguration[Configuration::KEY_ANNOTATIONS_CACHE];

        if (!array_key_exists(Configuration::KEY_CACHE_TYPE, $annotationsCacheSettings) || !array_key_exists(Configuration::KEY_CACHE_ID, $annotationsCacheSettings)) {

            //we're missing either the type or the id
            return false;
        }

        return true;
    }

    public function act(array $processedConfiguration, ContainerBuilder $container)
    {
        /**
         * Build a Doctrine cache.
         */
        $annotationsCacheServiceDefinition = $this->buildDoctrineCacheDefinition(

            $processedConfiguration[Configuration::KEY_ANNOTATIONS_CACHE],
            $container,
            'ehough_pagoda_box.annotations_cache'
        );

        if ($annotationsCacheServiceDefinition === null) {

            //couldn't build the cache for some reason.
            return;
        }

        /**
         * Register the cache.
         */
        $container->setDefinition(

            self::$_SERVICE_ID_ANNOTATIONS_READER_CACHE,
            $annotationsCacheServiceDefinition
        );

        /**
         * Register our cached reader.
         */
        $container->register(

            self::SERVICE_ID_ANNOTATIONS_READER,
            'Doctrine\Common\Annotations\CachedReader'

        )->addArgument(new Reference(self::ALIAS_ID_ORIGINAL_ANNOTATIONS_READER))
         ->addArgument(new Reference(self::$_SERVICE_ID_ANNOTATIONS_READER_CACHE))
         ->addArgument($container->getParameter('kernel.debug'));
    }
}
