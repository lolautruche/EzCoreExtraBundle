# UPGRADE FROM 2.x to 3.0

* Support for eZ Platform 2.x and 1.x has been dropped (and with them support for
  PHP < 7.3 and Symfony < 5.0)

* E-mail authentication providers have been removed since eZ Platform v3 has a similar
  feature implemeted directly in kernel and this implementation was not compatible with
  the new kernel

* Template variables injection feature as a whole has been deprecated since eZ Platform v3
  has a similar feature implemeted directly in kernel

* Access to `content` and `location` has been changed in view parameter providers
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
