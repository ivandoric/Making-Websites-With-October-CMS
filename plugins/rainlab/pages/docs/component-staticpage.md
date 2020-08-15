# Component: Static Page (staticPage)

## Purpose
Enables Static Pages to use the layout that includes this component.

## Available properties

Property | Inspector Name | Description
-------- | -------------- | -----------
`useContent` | Use page content field | If false, the content section will not appear when editing the static page. Page content will be determined solely through placeholders and variables.
`default` | Default layout | If true, defines this layout (the layout this component is included on) as the default for new pages
`childLayout` | Subpage layout | The layout to use as the default for any new subpages created from pages that use this layout

## Page variables

Variable | Type | Description
-------- | ---- | -----------
`page` | `RainLab\Pages\Classes\Page` | Reference to the current static page object
`title` | `string` | The title of the current static page
`extraData` | `array` | Any extra data defined in the page object (i.e. placeholders & variables defined in the layout)

## Default output

The default component partial outputs the rendered contents of the current Static Page. However, it's recommended to just use `{% page %}` to render the contents of the page instead to match up with how CMS pages are rendered.

## Default page layout

If adding a new subpage, the parent page's layout is checked for a `childLayout` property, and the new subpage's layout will default to that property value. Otherwise, the theme layouts will be searched for the `default` component property and that layout will be selected by default.

Example:
```
# /themes/mytheme/layouts/layout1.htm
[staticPage]
default = true
childLayout = "child"

# /themes/mytheme/layouts/child.htm
[staticPage]
```