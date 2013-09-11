<?php

namespace Ehough\Bundle\PagodaBoxBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AliasSwappingCompilerPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    private $_originalAliasId;

    /**
     * @var string
     */
    private $_newServiceId;

    public function __construct($originalAliasId, $newServiceId)
    {
        if (!is_string($originalAliasId)) {

            throw new \InvalidArgumentException('Original alias ID must be a string');
        }

        if (!is_string($newServiceId)) {

            throw new \InvalidArgumentException('New service ID must be a string');
        }

        $this->_originalAliasId = $originalAliasId;
        $this->_newServiceId    = $newServiceId;
    }

    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @api
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasAlias($this->_originalAliasId)
            || !$container->hasDefinition($this->_newServiceId)) {

            return;
        }

        $container->setAlias($this->_originalAliasId, new Alias($this->_newServiceId));
    }
}