# Template variables injection

In many cases, the default variables that are injected in the template to display a content/location is not sufficient
and will lead you to define custom controllers in order to access different parameters.

Typical use cases are access to:

* Settings (either coming from ConfigResolver or ServiceContainer)
* Current content's ContentType object
* Current location's parent
* Current's location children count
* Content owner
* etc...


## Description
This features adds the possibility to define Twig global variables depending on the current SiteAccess.
These global variables will be available in every templates.

It also adds a [generic subscriber to `ezpublish.pre_content_view` event](https://doc.ez.no/display/EZP/Parameters+injection+in+content+views),
bound to the template selection rules, so that you can inject configured parameters in the selected view.


## Context aware Twig global variables
When working on multisite instances, you may want to define variables that will be available only in the current
SiteAccess. By default, Twig allows to inject global variables for the whole application.

To define context aware Twig global variables, you need to configure them:

```yaml
ez_core_extra:
    system:
        my_siteaccess:
            twig_globals:
                my_variable: foo
                another_variable: 123
                something_else: [bar, true, false]
```

With this configuration, your variables will be accessible in any templates rendered in `my_siteaccess` context:

```jinja
My variable: {{ my_variable }}<br>
Number variable : {{ another_variable }}<br>
<br>
{% for val in something_else %}
    {{ val }}<br>
{% endfor %}
```

## Parameters injection in view templates
This functionality is meant for simple to intermediate needs.
The goal is to expose additional variables in your view templates (content, location, block...)
from the template selection rules configuration.

You can inject several types of parameters:

* Plain parameters, which values are directly defined in the configuration (including arrays, hashes, booleans…)
* Parameter references from the ServiceContainer (e.g. `%my.parameter%`)
* [Dynamic settings](https://doc.ez.no/display/EZP/Dynamic+settings+injection) (aka *siteaccess aware parameters*,
  using `$<paramName>[;<namespace>[;<scope>]]$` syntax)
* [Parameter provider services](#parameter-provider-services)

See [full example](#full-example) for practical details.

### Parameter provider services
In some cases settings are not sufficient (i.e. if you need to always have the current ContentType available, or the current children count).

Services cannot be directly injected. but you can define *view parameters provider* services,
meaning that they will provide the variables to inject in the view template.
Moreover, variables returned by those services will be *namespaced* by the parameter name provided in the configuration.

Parameter provider services must implement `\Lolautruche\EzCoreExtraBundle\Templating\ViewParameterProviderInterface` interface.
Such services must be defined with `ez_core_extra.view_parameter_provider` tag.

See the [full example below](#full-example), for details.


### Example
This feature would allow to configure a content/location/block view the following way:

```yaml
#ezpublish.yml
ezpublish:
    system:
        my_siteaccess:
            location_view:
                full:
                    article_test:
                        template: "AcmeTestBundle:full:article_test.html.twig"
                        params:
                            # This service must implement \Lolautruche\EzCoreExtraBundle\Templating\ViewParameterProviderInterface.
                            my_provider: {"provider": "my_param_provider"}
                            osTypes: [osx, linux, losedows]
                            secret: "%secret%"
                            # Parameters resolved by config resolver
                            # Supported syntax for parameters: $<paramName>[;<namespace>[;<scope>]]$
                            # e.g. full syntax: $my_setting;custom_namespace;my_siteaccess$
                            # See https://doc.ez.no/display/EZP/Dynamic+settings+injection
                            default_ttl: "$content.default_ttl$"
                        match:
                            Id\Location: 144
```

> **Important**: Note that all configured parameters are only available in the template spotted in the template selection rule.

#### Parameter provider example
In the configuration example above, `my_param_provider` would be like:

```php
<?php
namespace Acme\TestBundle;

use Lolautruche\EzCoreExtraBundle\Templating\ViewParameterProviderInterface;
use Acme\TestBundle\SomeService;

class MyViewParameterProvider implements ViewParameterProviderInterface
{
    private $someService;

    /**
     * Injected service is just an example. It can be whatever dependency you need
     */
    public function __construct(SomeService $someService)
    {
        $this->someService = $someService;
    }

    /**
     * Returns a hash of parameters to inject into the matched view.
     * Key is the parameter name, value is the parameter value.
     *
     * @param array $viewConfig Current view configuration hash.
     *                          Available keys:
     *                              - template: Template used for the view.
     *                              - parameters: Hash of parameters that will be passed to the template.
     *
     * @return array
     */
    public function getParameters(array $viewConfig)
    {
        // Current location and content are available in content/location views
        $location = $viewConfig['parameters']['location'];
        $content = $viewConfig['parameters']['content'];

        return array(
            'foo' => $this->someService->giveMeFoo(),
            'some' => 'thing'
        );
    }
}
```

Now defining the service:

```yaml
services:
    acme_test.my_provider:
        class: Acme\TestBundle\MyViewParameterProvider
        arguments: ["@some_service"]
        tags:
            # alias must match with value configured under "provider" key in ezpublish.yml
            - {name: "ez_core_extra.view_parameter_provider", alias: "my_param_provider"}
```

#### Resulting view template
The view template would then be like:

```jinja
{% extends "AcmeDemoBundle::pagelayout.html.twig" %}

{% block content %}
<h1>{{ ez_render_field( content, 'title' ) }}</h1>

<p><strong>Secret:</strong> {{ secret }}</p>

<p><strong>OS Types:</strong></p>
{% for os in osTypes %}
    {{ os }}
    {% if not loop.last %}, {% endif %}
{% endfor %}

{# "my_helper" is namespaced by "my_service" according to configuration #}
<p>{{ my_provider.foo }}</p>
<p>{{ my_provider.some }}</p>
{% endblock %}

```
