# Themes

This features allows you to provide themes to your eZ application, with automatic fallback system.
It is very similar to [legacy design fallback system](https://doc.ez.no/eZ-Publish/Technical-manual/5.x/Concepts-and-basics/Designs/Design-combinations).

When you call a given template, the system will look for it in the first configured theme. If it cannot be found, the
system will fallback to all other configured themes for your current SiteAccess.

Under the hood, theming system uses Twig namespaces. As such, Twig is the only supported template engine.

## Terminology
* **Design**: Collection of themes.
  The order of themes within a design is important as it defines the fallback order.
  A design is identified with a name. One design can be used by SiteAccess.

* **Theme**: Labeled collection of templates
  Typically a directory containing templates. For example, templates located under `app/Resources/views/themes/my_theme`
  or `src/AppBundle/Resources/views/themes/my_theme` are part of `my_theme` theme.

## Usage
> When using eZ Publish 5.x, replace `app/` directory by `ezpublish/`.

### Design configuration
To define and use a design, you need to:
0. Declare it, with a name and a collection of themes to use
0. Use it for your SiteAccess

Here is a simple example:
```yaml
ez_core_extra:
    design:
        # You declare every available designs under "list".
        list:
            # my_design will be composed of "theme1" and "theme2"
            # "theme1" will be the first tried. If the template cannot be found in "theme1", "theme2" will be tried out.
            my_design: [theme1, theme2]
    system:
        my_siteaccess:
            # my_siteaccess will use "my_design"
            design: my_design
```

### Templates
By convention, a theme directory must be located under `<bundle_directory>/Resources/views/themes/` or global
`app/Resources/views/themes/` directories.

Typical paths can be for example:
* `app/Resources/views/themes/foo/` => Templates will be part of `foo` theme.
* `app/Resources/views/themes/bar/` => Templates will be part of `bar` theme.
* `src/AppBundle/Resources/views/themes/foo/` => Templates will be part of `foo`theme.
* `src/Acme/TestBundle/Resources/views/themes/the_best/` => Templates will be part of `the_best` theme.

In order to use the configured design with templates, you need to use `@ezdesign` special Twig namespace.

```jinja
{# Will load 'some_template.html.twig' directly under one of the specified themes directories #}
{{ include("@ezdesign/some_template.html.twig") }}

{# Will load 'another_template.html.twig', located under 'full/' directory, which is located under one of the specified themes directories #}
{{ include("@ezdesign/full/another_template.html.twig") }}
```

You can also use `@ezdesign` notation in your eZ template selection rules:

```yaml
ezpublish:
    system:
        my_siteaccess:
            content_view:
                full:
                    home:
                        template: "@ezdesign/full/home.html.twig"
```

> You may also use this notation in controllers.

#### Fallback order
Default fallback order is the following:
* Global view override: `app/Resources/views/`
* Global theme override: `app/Resources/views/themes/<theme_name>/`
* Bundle theme directory: `src/<bundle_directory>/Resources/views/themes/<theme_name>/`

> The bundle fallback order is the instantiation order in `AppKernel` (or `EzPublishKernel` when using eZ Publish 5.x).

#### Additional override paths
It is possible to add addition global override directories, similar to `app/Resources/views/`.

```yaml
ez_core_extra:
    design:
        override_paths:
            - "%kernel.root_dir%/another_override_directory"
            - "/some/other/directory"
```

> `app/Resources/views/` will **always** be the top level override directory.

### Assets
For assets, a special `ezdesign` asset package is available.

```jinja
<script src="{{ asset("js/foo.js", "ezdesign") }}"></script>

<link rel="stylesheet" href="{{ asset("js/foo.css", "ezdesign") }}" media="screen" />

<img src="{{ asset("images/foo.png", "ezdesign") }}" alt="foo"/>
```

Using `ezdesign` package will resolve current design with theme fallback.

By convention, an asset theme directory must be located under `<bundle_directory>/Resources/public/themes/`.

Typical paths can be for example:
* `<bundle_directory>/Resources/public/themes/foo/` => Assets will be part of `foo` theme.
* `<bundle_directory>/Resources/public/themes/bar/` => Assets will be part of `bar` theme.

It is also possible to use the `web/assets` override directory. If called asset is present here, it will always be
considered first.

> **Important**: You must have *installed* your assets with `assets:install` command, so that your public resources are
> *installed* in the `web/` directory.

#### Fallback order
Default fallback order is the following:
* Global assets override: `web/assets/`
* Bundle theme directory: `web/bundles/<bundle_directory>/themes/<theme_name>/`

Calling `asset("js/foo.js", "ezdesign")` can for example be resolved to `web/bundles/app/themes/my_theme/js/foo.js`.
