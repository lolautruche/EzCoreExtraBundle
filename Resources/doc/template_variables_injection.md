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

It also adds a generic subscriber to `ezpublish.pre_content_view` event,  bound to the template selection rules,
so that you can inject configured parameters in the selected view.


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
* [Expressions](view_parameters_expressions.md) (for dynamic injection with ExpressionLanguage)
* [Parameters provider services](view_parameters_providers.md) (for more dynamic injection using custom reusable services)

### Example
This feature would allow to configure a content/location/block view the following way:

```yaml
ibexa:
    system:
        my_siteaccess:
            location_view:
                full:
                    article_test:
                        template: "@ibexadesign/full/article_test.html.twig"
                        params:
                            osTypes: [osx, linux, losedows]
                            secret: "%secret%"
                        match:
                            Id\Location: 144
```

> **Important**: Note that all configured parameters are only available in the template spotted in the template selection rule.

> For more advanced and dynamic injection, you may use **[Expressions](view_parameters_expressions.md)**
> or implement a **[ViewParametersProvider service](view_parameters_providers.md)**.

#### Resulting view template
The view template would then be like:

```jinja
{% extends "pagelayout.html.twig" %}

{% block content %}
    <h1>{{ ibexa_render_field(content, 'title') }}</h1>
    
    <p><strong>Secret:</strong> {{ secret }}</p>
    
    <p><strong>OS Types:</strong></p>
    {% for os in osTypes %}
        {{ os }}
        {% if not loop.last %}, {% endif %}
    {% endfor %}

{% endblock %}

```
