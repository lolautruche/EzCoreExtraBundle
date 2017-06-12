# UPGRADE FROM 1.x to 2.0

## Template variables injection
### Interface change for View parameters providers

`\Lolautruche\EzCoreExtraBundle\Templating\ViewParameterProviderInterface` has been deprecated in v1.1 in favor of
`\Lolautruche\EzCoreExtraBundle\View\ViewParameterProviderInterface`.
The old interface will be removed in v2.0. All view parameter providers now **must** implement the new one.

**Before**
```php
namespace Lolautruche\EzCoreExtraBundle\Templating;

/**
 * Interface for services that provides parameters to the view.
 *
 * @deprecated Since v1.1. Use \Lolautruche\EzCoreExtraBundle\View\ViewParameterProviderInterface instead.
 */
interface ViewParameterProviderInterface
{
    /**
     * Returns a hash of parameters to inject into the matched view.
     * Key is the parameter name, value is the parameter value.
     *
     * @param array $viewConfig Current view configuration hash.
     *                          Available keys:
     *                          - template: Template used for the view.
     *                          - parameters: Hash of parameters that will be passed to the template.
     *
     * @return array
     */
    public function getParameters(array $viewConfig);
}
```

**After**
```php
namespace Lolautruche\EzCoreExtraBundle\View;

use Lolautruche\EzCoreExtraBundle\Templating\ViewParameterProviderInterface as LegacyParamProviderInterface;

/**
 * Interface for services providing parameters to a view.
 */
interface ViewParameterProviderInterface extends LegacyParamProviderInterface
{
    /**
     * Returns a hash of parameters to inject into the matched view.
     * Key is the parameter name, value is the parameter value.
     *
     * Available view parameters (e.g. "content", "location"...) are accessible from $view.
     *
     * @param ConfigurableView $view Decorated matched view, containing initial parameters.
     * @param array $options
     *
     * @return array
     */
    public function getViewParameters(ConfigurableView $view, array $options = []);
}
```

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
