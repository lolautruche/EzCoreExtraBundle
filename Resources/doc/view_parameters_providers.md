# View parameters providers

For template variables injection, simple settings may not be sufficient 
(i.e. if you need to always have the current ContentType available, or the current children count).

By design, **services cannot be directly injected**. but you can define *view parameters provider* services,
which **goal is to provide variables to inject into view templates**.
Moreover, variables returned by those services will be *namespaced* by the parameter name provided in the configuration.

Parameters provider services must implement `Lolautruche\EzCoreExtraBundle\View\ViewParameterProviderInterface` interface
(or extend `Lolautruche\EzCoreExtraBundle\View\ConfigurableViewParameterProvider`).
Such services must be defined with `ez_core_extra.view_parameter_provider` tag.

A Parameters provider may expose options that one can set in the view configuration to alter the service behavior.

Cherry on the cake, such services are reusable across all the content views in your eZ application. 

## Parameters provider example

In the following example we define a `MetaDataProvider` that provides the `ContentType` of each viewed content.
It also provides the content author. By default it will load the content owner name, with the possibility to use an
author field (see exposed options).

```php
<?php

namespace AppBundle\Provider;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\UserService;
use Lolautruche\EzCoreExtraBundle\View\ConfigurableView;
use Lolautruche\EzCoreExtraBundle\View\ConfigurableViewParameterProvider;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MetaDataProvider extends ConfigurableViewParameterProvider
{
    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var ContentTypeService
     */
    private $contentTypeService;

    public function __construct(UserService $userService, ContentTypeService $contentTypeService)
    {
        $this->userService = $userService;
        $this->contentTypeService = $contentTypeService;
    }

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
    public function doGetParameters(ConfigurableView $view, array $options = [])
    {
        $content = $view->getContent();

        if ($options['use_author_field']) {
            $authorName = (string)$content->getFieldValue($options['author_field_name']);
        }

        return [
            'author' => $authorName ?? $this->userService->loadUser($content->contentInfo->ownerId)->getName(),
            'contentType' => $this->contentTypeService->loadContentType($content->contentInfo->contentTypeId),
        ];
    }

    /**
     * Configures the OptionsResolver for the param provider.
     *
     * Example:
     * ```php
     * // type setting will be required
     * $resolver->setRequired('type');
     * // limit setting will be optional, and will have a default value of 10
     * $resolver->setDefault('limit', 10);
     * ```
     *
     * @param OptionsResolver $optionsResolver
     */
    protected function configureOptions(OptionsResolver $optionsResolver)
    {
        $optionsResolver
            ->setDefaults([
                'use_author_field' => false,
                'author_field_name' => 'author',
            ]);
    }
}
```

### Service configuration
```yaml
services:
    app.metadata_provider:
        class: AppBundle\Provider\MetaDataProvider
        arguments: ['@ezpublish.api.service.user', '@ezpublish.api.service.content_type']
        tags:
            # By default the provider alias will be the service name, but you may customize it using "alias" tag attribute.
            - { name: ez_core_extra.view_parameter_provider }
```

Using Symfony 3.3+ it can be simplified thanks to autoconfiguration:

```yaml
services:
    _defaults:
        autoconfigure: true
        
    AppBundle\Provider\MetaDataProvider:
        arguments: ['@ezpublish.api.service.user', '@ezpublish.api.service.content_type']
```

### View configuration
```yaml
ezpublish:
    system:
        my_siteaccess:
            location_view:
                full:
                    article_test:
                        template: "@ezdesign/full/article_test.html.twig"
                        params:
                            # Key is the name of the variable "namespace" in the template (see template below)
                            metadata: 
                                # provider key corresponds to the provider service name (or alias if defined).
                                # When using FQCN as service name with Symfony 3.3+, just set the FQCN as value:
                                # provider: "AppBundle\Provider\MetaDataProvider"
                                provider: "app.metadata_provider"
                                options: 
                                    use_author_field: true
                                    author_field_name: author
                        match:
                            Id\Location: 144
```

### Resulting view
```jinja
{% extends "pagelayout.html.twig" %}

{% block content %}
<h1>{{ ez_render_field(content, 'title') }}</h1>

{# Param provider is namespaced by "metadata" according to configuration #}
<p>Author: {{ metadata.author }}</p>
<p>ContentType name: {{ metadata.contentType.name }}</p>
{% endblock %}
```
