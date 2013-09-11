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

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 *
 */
interface FeatureInterface
{
    /**
     * @param array            $processedConfiguration The processed and validated configuration for the bundle.
     * @param ContainerBuilder $container              The container.
     *
     * @return boolean True if the feature can and should act on the given configuration.
     */
    function shouldAct(array $processedConfiguration, ContainerBuilder $container);

    /**
     * Act on the given configuration.
     *
     * @param array            $processedConfiguration The processed and validated configuration for the bundle.
     * @param ContainerBuilder $container              The container.
     *
     * @return void
     */
    function act(array $processedConfiguration, ContainerBuilder $container);


}
