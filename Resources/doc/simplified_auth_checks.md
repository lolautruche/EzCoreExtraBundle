# Simplified authorization checks

This feature simplifies the way you check authorization with Ibexa inner ACL system, using
`module/function` and optionnaly a value object (e.g. a content object).

Without eZCoreExtraBundle, when one want to check if a user has access to a module/function like
`content/read`, they have to implement the following in their controller:

```php
namespace Acme\Controller;

use Ibexa\Bundle\Core\Controller;
use Ibexa\Core\MVC\Symfony\Security\Authorization\Attribute as AuthorizationAttribute;

class MyController extends Controller
{
    public function fooAction()
    {
        // ...
        $accessGranted = $this->isGranted(new AuthorizationAttribute('content', 'read'));
        
        // Or with an actual content
        $accessGranted = $this->isGranted(
            new AuthorizationAttribute('content', 'read', ['valueObject' => $myContent])
        );
    }
}
```

While this is efficient, it is a bit cumbersome to write.
Furthermore, it's not possible to do such security checks within Twig templates, as it's not possible
to instantiate new objects from there.

EzCoreExtraBundle adds a new simplified syntax for such checks, usable in templates.

## Usage
In order to check access for a `module`/`function` pair, instead of instantiating an `AuthorizationAttribute`
object, just use the following syntax:

```
ez:<module>:<function>
```

Taking the example from the introduction, it will be:

```php
namespace Acme\Controller;

use Ibexa\Bundle\Core\Controller;

class MyController extends Controller
{
    public function fooAction()
    {
        // ...
        $accessGranted = $this->isGranted('ez:content:read');
        
        // Or with an actual content
        $accessGranted = $this->isGranted('ez:content:read', $myContent);
    }
}
```

In a template, the syntax will be:

```jinja
{% set accessGranted = is_granted('ez:content:read') %}

{# Or with an actual content #}
{% set accessGranted = is_granted('ez:content:read', my_content) %}
```

Et voilà :-)
