## EhoughEmailErrorsBundle [![Build Status](https://secure.travis-ci.org/ehough/pagodabox-bundle.png)](http://travis-ci.org/ehough/pagodabox-bundle)
A Symfony2 bundle that makes it easy to configure and deploy your app on [Pagoda Box](https://pagodabox.com/).

####Features

* Focus on high-performance
  * Never use shared writable directories for Symfony's cache ([why?](http://blog.pagodabox.com/shared-writable-storage-interruption/))
  * Store PHP sessions in Redis ([why?](http://blog.pagodabox.com/store-sessions-redis-shared-writable-storage/))
* Easily configure Doctrine to use Pagoda Box caches (with no [extra setup](https://github.com/pagodabox/symfony-demo))
* Full composer support - optionally use your GitHub OAuth token during deployment
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

###Redis Session Storage
Pagoda Box [strongly recommends](http://blog.pagodabox.com/store-sessions-redis-shared-writable-storage/) that you
utilize a Redis cache for session storage. This bundle makes this task as easy as possible.

#####`Boxfile`
```yml
web1:

  php_extensions:
    -redis

  php_session_save_handler : redis
  php_session_save_path : "tcp://tunnel.pagodabox.com:6379"
```

#####`app/config_prod.yml`
```yml
ehough_pagoda_box:

  use_redis_for_sessions : true
```

That's it! Your Symony sessions will now magically be stored safely in Redis.

###Annotations Caching
By default, Symfony will use a [file-based cache](http://symfony.com/doc/current/reference/configuration/framework.html#full-default-configuration)
for its annotations cache. With this bundle we can easily utilize a Pagoda Box cache instead.

#####`Boxfile`
```yml
web1:

  php_extensions:    # at least one of these extensions, depending on your app/config_prod.yml
    -redis
    -memcache
    -memcached

  php_session_save_handler : redis
  php_session_save_path : "tcp://tunnel.pagodabox.com:6379"
```

#####`app/config_prod.yml`
```yml
ehough_pagoda_box:

  annotations_cache:

    type : redis                    # memcache, memcached, or redis
    pagoda_env_id : CACHE2          # the Pagoda Box cache ID you'd like to use for the annotations cache
```
The `type` parameter can have a value of `memcache`, `memcached`, or `redis`, depending on the actual type of the
cache you identify in the `pagoda_env_id` parameter.

###Doctrine DBAL Connection Mapping
Pagoda Box [provides instructions](https://github.com/pagodabox/symfony-demo/blob/master/README.mkd)
on how to configure your Symfony database for their platform. However, this bundle makes the process much easier.

#####`app/config_prod.yml`
```yml
ehough_pagoda_box:

  doctrine:
    dbal:
      connections:
        default: DB1
```
As you can see in the above  `app/config_prod.yml`, you define a map of Doctrine DBAL connection identifiers
to their corresponding Pagoda Box databases. In the example above, we are mapping the `default` DBAL connection
to `DB1`. i.e. The `default` connection will receive the values of the environment variables `DB1_HOST`, `DB1_PORT`, etc.

In most cases, you will simply need to configure the `default` DBAL connection, but you can define as many connection
mappings as you like. e.g.

```yml
ehough_pagoda_box:

  doctrine:
    dbal:
      connections:
        default: DB1
        other: DB2
        another: DB3
```

###Configuration Reference

```yml
ehough_pagoda_box:

  use_redis_for_sessions: true      # use Redis for session storage?

  annotations_cache:

    type : redis                    # memcache, memcached, or redis
    pagoda_env_id : CACHE2          # the Pagoda Box cache ID

  doctrine:

	dbal:
	  connections:                  # a map of Doctrine DBAL connection IDs to Pagoda Box database IDs
	    default : DB1               # maps the "default" Doctrine DBAL connection to DB1_HOST, DB1_PORT, etc
	    other : DB2                 # maps the "other" Doctrine DBAL connection to DB2_HOST, DB2_PORT, etc

	orm:
	  caching:                      # a map of Doctrine ORM entity manager IDs to
	    default:
		  metadata:                 # metadata, query, or result
		    type: memcache          # memcache or memcached
      		pagoda_env_id: CACHE3 	# the Pagoda Box cache ID. This must be a Memcache cache!
          query:
            type: memcache
            pagoda_env_id: CACHE4
          result:
            type: memcache
            pagoda_env_id: CACHE5
        my_em:
          metadata:
            ...
```