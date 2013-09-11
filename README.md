## EhoughEmailErrorsBundle [![Build Status](https://secure.travis-ci.org/ehough/pagodabox-bundle.png)](http://travis-ci.org/ehough/pagodabox-bundle)
A Symfony2 bundle that makes it easy to configure and deploy your app on [Pagoda Box](https://pagodabox.com/).

####Features

* Focus on high-performance
  * Never use shared writable directories ([why?](http://blog.pagodabox.com/shared-writable-storage-interruption/))
  * Store PHP sessions in Redis ([why?](http://blog.pagodabox.com/store-sessions-redis-shared-writable-storage/))
* Easily configure Doctrine to use Pagoda Box caches (with no [extra setup](https://github.com/pagodabox/symfony-demo))
* Excellent test coverage - ready for production

###Requirements
* Symfony 2.3+

###Installation
Add to your `composer.json`

 ```json
"require" : {
    "ehough/pagodabox-bundle" : "dev-master"
}
```

Then register the bundle in `app/AppKernel.php`.

```php
public function registerBundles()
{
    return array(

        // ...
        new Ehough\Bundle\PagodaBoxBundle\EhoughPagodaBoxBundle()
    );
}
```

###Configuration
In your app's `config.yml` (or `config_prod.yml`)

```yml
ehough_pagoda_box:

  use_redis_for_sessions: true      # use Redis for session storage?

  annotations_cache:

    type: redis                     # memcache, memcached, or redis
    pagodabox_id: CACHE2            # the cache's environment variable prefix from Pagoda Box

  doctrine:

	dbal:
	  connections:                  # a map of Doctrine DBAL connection IDs to Pagoda Box database IDs
	    default : DB1               # maps the "default" Doctrine DBAL connection to DB1_HOST, DB1_PORT, etc

	orm:
	  caching:
	    default:                    # a map of Doctrine ORM entity manager IDs to
		  metadata:                 # metadata, query, or result
		    type: memcache          # memcache or memcached
      		pagodabox_id: CACHE3 	# the environment variable from Pagoda Box. This must be a Memcache cache!
```