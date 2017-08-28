# UPGRADE FROM 2.x to 3.0

## Template variables injection
### View parameter providers

#### Access to `content` and `location`
Access to current `Content` and `Location` objects have changed.
They're now available via `ConfigurableView::getContent()` and `ConfigurableView::getLocation`.

**Before**

```php
class MyParamProvider
{
    public function getViewParameters(ConfigurableView $view, array $options = [])
    {
        // Current location and content are available in content/location views
        $location = $view->getParameter('content');
        $content = $view->getParameter('location');
        
        // Passed options
        $contentTypeForChildren = $options['children_type'];
        $childrenLimit = $options['children_limit'];
        // Fetch children with those options
        // $fetchedChildren = ...

        return array(
            'foo' => $this->someService->giveMeFoo(),
            'some' => 'thing',
            'children' => $fetchedChildren,
        );
    }
}}
```

**After**

```php
class MyParamProvider
{
    public function getViewParameters(ConfigurableView $view, array $options = [])
    {
        // Current location and content are available in content/location views
        $location = $view->getContent();
        $content = $view->getLocation();
        
        // Passed options
        $contentTypeForChildren = $options['children_type'];
        $childrenLimit = $options['children_limit'];
        // Fetch children with those options
        // $fetchedChildren = ...

        return array(
            'foo' => $this->someService->giveMeFoo(),
            'some' => 'thing',
            'children' => $fetchedChildren,
        );
    }
}}
```
