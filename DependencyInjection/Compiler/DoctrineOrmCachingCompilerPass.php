<?php
/**
 * Copyright 2006 - 2013 Eric D. Hough (http://ehough.com)
 *
 * This file is part of coauthor (https://github.com/ehough/coauthor)
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
namespace Ehough\Bundle\PagodaBoxBundle\DependencyInjection\Compiler;

use Ehough\Bundle\PagodaBoxBundle\DependencyInjection\Features\DoctrineOrmCacheFeature;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class DoctrineOrmCachingCompilerPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @api
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasParameter(DoctrineOrmCacheFeature::CACHE_MAP_ID)) {

            //the cache map was never set for some reason... that's OK
            return;
        }

        $map = $container->getParameter(DoctrineOrmCacheFeature::CACHE_MAP_ID);

        foreach ($map as $emName => $cacheDefinitionMap) {

            foreach ($cacheDefinitionMap as $cacheName => $newCacheId) {

                $cacheIdToReplace = 'doctrine.orm.' . $emName . '_' . $cacheName . '_cache';

                if (!$container->hasDefinition($cacheIdToReplace) || !$container->hasDefinition($newCacheId)) {

                    continue;
                }

                $container->setDefinition($cacheIdToReplace, $container->getDefinition($newCacheId));
            }
        }
    }
}