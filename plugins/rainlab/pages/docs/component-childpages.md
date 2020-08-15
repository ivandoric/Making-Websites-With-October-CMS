# Component: Child Pages (childPages)

## Purpose
Outputs a list of child pages of the current page

## Default output

The default component partial outputs a simple nested unordered list:

```html
<ul>
    <li>
        <a href="{{ page.url | app }}">{{ page.title }}</a>
    </li>
</ul>
```

You might want to render the list with your own code. The `childPages.pages` variable is an array of arrays representing the child pages. Each of the arrays has the following items:

Property | Type | Description
-------- | ---- | -----------
`url` | `string` | The relative URL for the page (use `{{ url | app }}` to get the absolute URL)
`title` | `string` | Page title
`page` | `RainLab\Pages\Classes\Page` | The page object itself
`viewBag` | `array` | Contains all the extra data used by the page
`is_hidden` | `bool` | Whether the page is hidden (only accessible to backend users)
`navigation_hidden` | `bool` | Whether the page is hidden in automaticaly generated contexts (i.e menu)

## Example of custom markup for component

```html
{% for page in childPages.pages %}
    <li><a href="{{ page.url | app }}">{{ page.title }}</a></li>
{% endfor %}
```