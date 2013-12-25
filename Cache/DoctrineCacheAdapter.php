<?php
namespace Ehough\Bundle\PagodaBoxBundle\Cache;

use Metadata\Cache\DoctrineCacheAdapter as BaseDoctrineCacheAdapter;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DoctrineCacheAdapter extends BaseDoctrineCacheAdapter
{
    public function __construct($prefix, ContainerInterface $container)
    {
        parent::__construct($prefix, $container->get('doctrine.orm.default_metadata_cache'));
    }
}
