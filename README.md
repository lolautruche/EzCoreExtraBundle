# EzCoreExtraBundle

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/4c330566-a5a9-45c1-82a5-00d781f355a0/mini.png)](https://insight.sensiolabs.com/projects/4c330566-a5a9-45c1-82a5-00d781f355a0)
[![Build Status](https://travis-ci.org/lolautruche/EzCoreExtraBundle.svg)](http://travis-ci.org/lolautruche/EzCoreExtraBundle)

Adds extra features to eZ Publish 5.4 / eZ Platform.

## Features
* **Configurable template variable injection**
  Lets you configure variables to inject within your view template configuration.
  This avoids you to create custom controllers when you need to add a few simple variables to your view.

  ```yaml
  ezpublish:
      system:
          my_siteaccess:
              location_view:
                  full:
                      article_test:
                          template: "AcmeTestBundle:full:article_test.html.twig"
                          params:
                              # Following keys will be injected as variables into configured template
                              osTypes: [osx, linux, losedows]
                              secret: %secret%
                              # Parameters resolved by config resolver
                              # See https://doc.ez.no/display/EZP/Dynamic+settings+injection
                              default_ttl: $content.default_ttl$
                              # Using a parameter provider, for more complex usecases.
                              my_provider: {"provider": "my_param_provider"}

                          match:
                              Id\Location: 144
  ```

## Requirements
EzCoreExtraBundle currently works with **eZ Publish 5.4/2014.11** (and *should work* with Netgen variant)
and eZ Platform (kernel version >=6.0).

## Installation
This bundle is available on [Packagist](https://packagist.org/packages/lolautruche/ez-core-extra-bundle).
You can install it using Composer.

```
composer require lolautruche/ez-core-extra-bundle
```

Then add it to your application:

```php
// ezpublish/EzPublishKernel.php

public function registerBundles()
{
    $bundles = [
        // ...
        new Lolautruche\EzCoreExtraBundle\EzCoreExtraBundle(),
        // ...
    ];
}
```

## Documentation
See [Resources/doc/](Resources/doc)
