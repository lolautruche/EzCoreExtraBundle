# UPGRADE FROM 3.x to 4.0

* Support for eZ Platform 3.x has been dropped (and with it support for PHP < 7.4 and Symfony < 5.4)

* Renamed the prefix for simplified role check from `ez:` to `ibexa:`

  **Before**

  ```
  {% if is_granted('ez:user:register') %}
      ...
  {% endif %}
  ```

  **After**

  ```
  {% if is_granted('ibexa:user:register') %}
      ...
  {% endif %}
  ```
