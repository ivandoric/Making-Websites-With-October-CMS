# Component: Static Menu (staticMenu)

## Purpose
Outputs a breadcrumb navigation for the current static page

## Page variables

Variable | Type | Description
-------- | ---- | -----------
`breadcrumbs` | `array` | Array of `RainLab\Pages\Classes\MenuItemReference` objects representing the defined menu

## Default output

The default component partial outputs a simple unordered list for breadcrumbs:

```twig
{% if breadcrumbs %}
    <ul>
        {% for breadcrumb in breadcrumbs %}
            <li class="{{ breadcrumb.isActive ? 'active' : '' }}">
                <a href="{{ breadcrumb.url }}">{{ breadcrumb.title }}</a>
            </li>
        {% endfor %}
    </ul>
{% endif %}
```

You might want to render the breadcrumbs with your own code. The `breadcrumbs` variable is an array of the `RainLab\Pages\Classes\MenuItemReference` objects. Each object has the following properties:

Property | Type | Description
-------- | ---- | -----------
`title` | `string` | Menu item title
`url` | `string` | Absolute menu item URL
`isActive` | `bool` | Indicates whether the item corresponds to a page currently being viewed
`isChildActive` | `bool` | Indicates whether the item contains an active subitem.
`items` | `array` | The menu item subitems, if any. If there are no subitems, the array is empty

## Example of custom markup for component

```html
{% for item in staticBreadCrumbs.breadcrumbs %}
    <li><a href="{{ item.url }}">{{ item.title }}</a></li>
{% endfor %}
```