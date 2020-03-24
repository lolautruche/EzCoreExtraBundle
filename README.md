# EzCoreExtraBundle

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/4c330566-a5a9-45c1-82a5-00d781f355a0/mini.png)](https://insight.sensiolabs.com/projects/4c330566-a5a9-45c1-82a5-00d781f355a0)
[![Build Status](https://travis-ci.org/lolautruche/EzCoreExtraBundle.svg)](http://travis-ci.org/lolautruche/EzCoreExtraBundle)

Adds extra features to eZ Platform.

## Compatibility
* `master` branch is **only compatible with eZ Platform v3**
* `v2.x` is **only compatible with eZ Platform v1 and v2**
* `1.0` branch is compatible with both eZ Platform *and* eZ Publish 5.4/2014.11.

## Features
* **[Configurable template variable injection](Resources/doc/template_variables_injection.md)**

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

* **[Context aware Twig global variables](Resources/doc/template_variables_injection.md)**

  Lets you define Twig global variables that will be available only in the current SiteAccess.

  ```yaml
  ez_core_extra:
      system:
          my_siteaccess:
              twig_globals:
                  my_variable: foo
                  another_variable: 123
                  something_else: [bar, true, false]
  ```

* **[Simplified authorization checks](Resources/doc/simplified_auth_checks.md)**

  Simplifies calls to `$this->isGranted()` from inside controllers and `is_granted()` from within templates when checking
  against eZ inner permission system (module/function/valueObject).

  ```

## Requirements
EzCoreExtraBundle currently works eZ Platform v1, v2 and v3 (kernel v6, v7 and v8).

> If you're using eZ publish 5.4/2014.11 or Netgen variant, look at `1.1` branch and/or `v1.x` releases.

> If you're using eZ Platform v1 or v2, look at `2.x` branch and/or `v2.x` releases.

## Installation
This bundle is available on [Packagist](https://packagist.org/packages/lolautruche/ez-core-extra-bundle).
You can install it using Composer.

```
composer require lolautruche/ez-core-extra-bundle
```

Then add it to your application:

> `EzCoreExtraBundle` **MUST** be instanciated **AFTER** eZ bundles.

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
