## EhoughPagodaBoxBundle [![Build Status](https://secure.travis-ci.org/ehough/EhoughPagodaBoxBundle.png)](http://travis-ci.org/ehough/EhoughPagodaBoxBundle)
A Symfony2 bundle that makes it easy to configure and deploy your app on [Pagoda Box](https://pagodabox.com/).

####Features

* Focus on high-performance
  * Never use shared writable directories for Symfony's cache ([why?](http://blog.pagodabox.com/shared-writable-storage-interruption/))
  * Store PHP sessions in Redis ([why?](http://blog.pagodabox.com/store-sessions-redis-shared-writable-storage/))
* Easily configure Doctrine connection parameters with [no extra setup](https://github.com/pagodabox/symfony-demo/blob/master/README.mkd)
* Easily configure Doctrine to utilize Pagoda Box caches
* Full composer support
  * Don't check in your `vendor` directory! This bundle installs your dependencies during the build on Pagoda Box.
  * Optionally utilize a GitHub OAuth token to prevent timeouts
* Excellent test coverage - ready for production

###Requirements
* Symfony 2.3+

###Installation
Add to your `composer.json`:

 ```json
"require": {
    "ehough/pagodabox-bundle": "dev-master"
}
```

Then register the bundle in `app/AppKernel.php`:

```php
public function registerBundles()
{
    return array(

        // ...
        new Ehough\Bundle\PagodaBoxBundle\EhoughPagodaBoxBundle()
    );
}
```

###Feature: Easy Application Deployment

Perhaps the most valuable feature of this bundle is the ability to properly deploy a composer-based Symfony2 app
to Pagoda Box. Simply add the following two `after_build` steps:

#####`Boxfile`
```yml
web1:

  shared_writable_dirs:
    -<relative path to your Symfony app root>/app/logs    # notice: no app/cache directory!

  after_build:
    - wget --quiet https://raw.github.com/ehough/EhoughPagodaBoxBundle/master/Resources/bash/gopagoda.sh
    - bash ./gopagoda.sh <relative path to your Symfony app root> <optional GitHub OAuth token>
```
The two `after_build` steps above effectively download and execute `gopagoda.sh`, which will do the
following for you:

1. Download `composer.phar` from getcomposer.org.
1. If you supplied a GitHub OAuth token, configure composer to utilize it.
1. Install your app's dependencies (`composer install`)
1. Create an optimized classloader for maximum classloading peformance (`composer dump-autoload --optimize`)
1. Build any assetic assets (`app/console" "assetic:dump" --env=prod`)
1. Clear any leftover Symfony cache (`app/console" "cache:clear" --env=prod`)
1. Warm the Symfony cache (`app/console" "cache:warmup" --env=prod`)
1. Trigger an initial HTTP request to Symfony to finish warming the cache (`php web/app.php`)

This will fully prepare your Symfony2 app for production before it's deployed to its final web server. Notice that
we are *not* using the `app/cache` directory for shared writable storage. Neat!

###Feature: Redis Session Storage
Pagoda Box [strongly recommends](http://blog.pagodabox.com/store-sessions-redis-shared-writable-storage/) that you
utilize a Redis cache for session storage. This bundle makes this task as easy as possible.

#####`Boxfile`
```yml
web1:

  php_extensions:
    -redis

  php_session_save_handler: redis
  php_session_save_path: "tcp://tunnel.pagodabox.com:6379"
```

#####`app/config_prod.yml`
```yml
ehough_pagoda_box:

  store_sessions_in_redis: true
```

That's it! Your Symony sessions will now magically be stored safely in Redis.

###Feature: Better Annotations Caching
By default, Symfony will use a [file-based cache](http://symfony.com/doc/current/reference/configuration/framework.html#full-default-configuration)
for its annotations cache. With this bundle we can easily utilize a Pagoda Box cache instead.

#####`Boxfile`
```yml
web1:

  php_extensions: # at least one of these
    -memcache
    -memcached
```

#####`app/config_prod.yml`
```yml
ehough_pagoda_box:

  annotations_cache:
    type: memcached         # memcache or memcached
    pagoda_env_id: CACHE2   # the Pagoda Box cache ID you'd like to use for the annotations cache
```
The `type` parameter can have a value of `memcache`, `memcached`, or `redis`, depending on the actual type of the
cache you identify in the `pagoda_env_id` parameter.

###Feature: Easier Doctrine Connection Configuration
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

###Feature: Easier Doctrine Cache Configuration
Out of the box, [Symfony provides in-memory caches](http://symfony.com/doc/current/reference/configuration/doctrine.html)
for [Doctrine's query, result, and metadata caches](http://docs.doctrine-project.org/en/latest/reference/caching.html#integrating-with-the-orm).
With this bundle, we can easily configure Doctrine to utilize any number of Pagoda Box memcached instances instead.

#####`Boxfile`
```yml
web1:

  php_extensions: # at least one of these
    -redis
    -memcache
    -memcached
```

#####`app/config_prod.yml`
```yml
ehough_pagoda_box:
  doctrine:
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
```
In the example above, we are configuring the `default` entity manager's metadata, query, and result cache. Each
cache accepts a type (`memcache` or `memcached`) as well as the Pagoda Box memcached instance identifier.

###Configuration Reference

```yml
ehough_pagoda_box:

  store_sessions_in_redis: true     # use Redis for session storage?

  annotations_cache:

    type: redis                     # memcache or memcached
    pagoda_env_id: CACHE2           # the Pagoda Box cache ID

  doctrine:

	dbal:
	  connections:                  # a map of Doctrine DBAL connection IDs to Pagoda Box database IDs
	    default: DB1                # maps the "default" Doctrine DBAL connection to DB1_HOST, DB1_PORT, etc
	    other: DB2                  # maps the "other" Doctrine DBAL connection to DB2_HOST, DB2_PORT, etc

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