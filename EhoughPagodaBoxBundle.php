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
namespace Ehough\Bundle\PagodaBoxBundle;

use Ehough\Bundle\PagodaBoxBundle\DependencyInjection\Compiler\AliasSwappingCompilerPass;
use Ehough\Bundle\PagodaBoxBundle\DependencyInjection\Compiler\DoctrineConnectionMappingCompilerPass;
use Ehough\Bundle\PagodaBoxBundle\DependencyInjection\Compiler\DoctrineOrmCachingCompilerPass;
use Ehough\Bundle\PagodaBoxBundle\DependencyInjection\Features\AnnotationsCacheFeature;
use Ehough\Bundle\PagodaBoxBundle\DependencyInjection\Features\RedisSessionHandlerFeature;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EhoughPagodaBoxBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $compilerPasses = $this->_getCompilerPassArray();

        foreach ($compilerPasses as $compilerPass) {

            $container->addCompilerPass($compilerPass);
        }
    }

    /**
     * @return \Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface[]
     */
    private function _getCompilerPassArray()
    {
        return array(

            new AliasSwappingCompilerPass(

                RedisSessionHandlerFeature::ALIAS_ID_ORIGINAL_SESSION_HANDLER,
                RedisSessionHandlerFeature::SERVICE_ID_SESSION_HANDLER
            ),
            new AliasSwappingCompilerPass(

                AnnotationsCacheFeature::ALIAS_ID_ORIGINAL_ANNOTATIONS_READER,
                AnnotationsCacheFeature::SERVICE_ID_ANNOTATIONS_READER),

            new DoctrineConnectionMappingCompilerPass(),
            new DoctrineOrmCachingCompilerPass(),
        );
    }
}