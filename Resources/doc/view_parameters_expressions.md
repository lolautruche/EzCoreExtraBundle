# View parameters expressions

Instead of injecting plain parameters, or dynamic settings into view variables, it is possible to use expressions
that will be evaluated in context before being injected.

This feature is written on top of [Symfony ExpressionLanguage component](https://symfony.com/doc/current/components/expression_language.html).

## Example

```yaml
ibexa:
    system:
        my_siteaccess:
            location_view:
                full:
                    article_test:
                        template: "@ibexadesign/full/article_test.html.twig"
                        params:
                            parentLocation:
                                expression: "loadLocation(location.parentLocationId)"
                            metadata:
                                expression: "{
                                    'contentTypeIdentifier': contentType.identifier,
                                    'section': repository.getSectionService().loadSection(content.contentInfo.sectionId),
                                    'owner': repository.getUserService().loadUser(content.contentInfo.ownerId),
                                    'secret': '%secret%'
                                }"
                        match:
                            Id\Location: 144
```

## Exposed variables and functions
In order to build your expressions, several variables and functions are exposed

### Variables
| Variable name    | Type                                                      | Description                           |
|------------------|-----------------------------------------------------------|---------------------------------------|
| `view`           | `Lolautruche\EzCoreExtraBundle\View\ConfigurableView`     | The **content view** being configured |
| `content`        | `Ibexa\Core\Repository\Values\Content\Content`            | Current content                       |
| `location`       | `Ibexa\Core\Repository\Values\Content\Location`           | Current location                      |
| `contentType`    | `Ibexa\Core\Repository\Values\ContentType\ContentType`    | ContentType of the current content    |
| `configResolver` | `Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface` | The ConfigResolver                    |
| `repository`     | `Ibexa\Core\Repository\Repository`                        | The content repository                |

### Functions
| Function name     | Description                      |
|-------------------|----------------------------------|
| `loadLocation`    | Loads a location object by ID    |
| `loadContent`     | Loads a content object by ID     |
| `loadContentType` | Loads a contentType object by ID |
