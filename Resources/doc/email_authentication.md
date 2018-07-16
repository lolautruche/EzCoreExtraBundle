# Authentication by e-mail

By default, eZ users can only authenticate using their username. However, using e-mail for authentication is quite a
common use case.

EzCoreExtraBundle enables the possibility for any eZ user to authenticate against their e-mail, in addition to their username.

You can easily activate it for your SiteAccess using the following config, where `my_siteaccess` is the name of
your SiteAccess or SiteAccess group:

```yaml
ez_core_extra:
    system:
        my_siteaccess:
            enable_email_authentication: true
```

Original behavior - authentication by username - is kept and will always have precedence (e.g. username will always
be tested first).

> **Important note**: `EzCoreExtraBundle` **MUST** be instanciated 
> **after eZ bundles** in `AppKernel`.
