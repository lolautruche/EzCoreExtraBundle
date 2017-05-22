# UPGRADE FROM 1.x to 2.0

## Themes
Themes are no longer part of EzCoreExtraBundle. 
This feature has been extracted to [ezsystems/ezplatform-design-engine](https://github.com/ezsystems/ezplatform-design-engine).

### Config migration
#### Design declaration, fallback and selection for a SiteAccess

> **Important**: For new configuration to work, you MUST install `ezsystems/ezplatform-design-engine`.

**Before**
```yaml
ez_core_extra:
    design:
        list:
            my_design: [theme1, theme2]
            
    system:
        my_siteaccess:
            design: my_design
```

**After**
```yaml
ezdesign:
    design_list:
        my_design: [theme1, theme2]

ezpublish:
    # ...
    system:
        my_siteaccess:
            design: my_design
```

#### PHPStorm support
**Before**
```yaml
ez_core_extra:
    phpstorm:

        # Activates PHPStorm support
        enabled:              '%kernel.debug%'

        # Path where to store PHPStorm configuration file for additional Twig namespaces (ide-twig.json).
        twig_config_path:     '%kernel.root_dir%/..'
```

**After**
```yaml
ezdesign:
    phpstorm:

        # Activates PHPStorm support
        enabled:              '%kernel.debug%'

        # Path where to store PHPStorm configuration file for additional Twig namespaces (ide-twig.json).
        twig_config_path:     '%kernel.root_dir%/..'
```

#### Additional template override paths

**Before**
```yaml
ez_core_extra:
    design:
        override_paths:
            - "%kernel.root_dir%/another_override_directory"
            - "/some/other/directory"
```

**After**
```yaml
ezdesign:
    template_override_paths:
        - "%kernel.root_dir%/another_override_directory"
        - "/some/other/directory"
```

#### Assets pre-resolution
**Before**
```yaml
# ezplatform_prod.yml
ez_core_extra:
    # Force runtime resolution
    # Default value is '%kernel.debug%'
    disable_assets_pre_resolution: true
```

**After**
```yaml
# ezplatform_prod.yml
ezdesign:
    # Force runtime resolution
    # Default value is '%kernel.debug%'
    disable_assets_pre_resolution: true
```
