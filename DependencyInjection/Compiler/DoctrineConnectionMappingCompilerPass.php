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

use Ehough\Bundle\PagodaBoxBundle\DependencyInjection\Features\DoctrineConnectionMapFeature;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DoctrineConnectionMappingCompilerPass implements CompilerPassInterface
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
        if (!$container->hasParameter(DoctrineConnectionMapFeature::CONNECTION_MAP_ID)) {

            return;
        }

        $map = $container->getParameter(DoctrineConnectionMapFeature::CONNECTION_MAP_ID);

        foreach ($map as $connectionName => $newParams) {

            $connectionId = 'doctrine.dbal.' . $connectionName . '_connection';

            if (!$container->hasDefinition($connectionId)) {

                continue;
            }

            $doctrineDbalConnection = $container->getDefinition($connectionId);
            $existingParams         = $doctrineDbalConnection->getArgument(0);

            $existingParams['dbname']   = $newParams[DoctrineConnectionMapFeature::CONNECTION_KEY_NAME];
            $existingParams['host']     = $newParams[DoctrineConnectionMapFeature::CONNECTION_KEY_HOST];
            $existingParams['port']     = $newParams[DoctrineConnectionMapFeature::CONNECTION_KEY_PORT];
            $existingParams['password'] = $newParams[DoctrineConnectionMapFeature::CONNECTION_KEY_PASS];
            $existingParams['user']     = $newParams[DoctrineConnectionMapFeature::CONNECTION_KEY_USER];

            $doctrineDbalConnection->replaceArgument(0, $existingParams);
        }
    }
}