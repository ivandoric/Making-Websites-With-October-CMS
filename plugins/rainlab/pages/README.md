# Pages plugin

This plugin allows end users to create and edit static pages and menus with a simple WYSIWYG user interface.

## Managing static pages

Static pages are managed on the Pages tab of the Static Pages plugin. Static pages have three required parameters - **Title**, **URL** and **Layout**. The URL is generated automatically when the Title is entered, but it could be changed manually. URLs must start with the forward slash character. The Layout drop-down allows to select a layout created with the CMS. Only layouts that include the `staticPage` component are displayed in the drop-down.

![image](https://raw.githubusercontent.com/rainlab/pages-plugin/master/docs/images/static-page.png) {.img-responsive .frame}

Pages are hierarchical. The page hierarchy is used when a new page URL is generated, but as URLs can be changed manually, the hierarchy doesn't really affect the routing process. The only place where the page hierarchy matters is the generated Menus. The generated menus reflect the page hierarchy. You can manage the page hierarchy by dragging pages in the page tree. The page drag handle appears when you move the mouse cursor over page item in the tree.

Optional properties of static pages are **Hidden** and **Hide in navigation**. The **Hidden** checkbox allows to hide a page from the front-end. Hidden pages are still visible for administrators who are logged into the back-end. The **Hide in navigation** checkbox allows to hide a page from generated menus and breadcrumbs.

## Placeholders

If a static layout contains [placeholders](https://octobercms.com/docs/cms/layouts#placeholders), the static page editor will show tabs for editing the placeholder contents. The plugin automatically detects text and HTML placeholders and displays a corresponding editor for them - the WYSIWYG editor for HTML placeholders and a text editor for text placeholders.

## Snippets

Snippets are elements that can be added by a Static Page, in the rich text editor. They allow to inject complex (and interactive) areas to pages. There are many possible applications and examples of using Snippets:

* Google Maps snippet - outputs a map centered on specific coordinates with predefined zoom factor. That snippet would be great for static pages that explain directions.
* Universal commenting system - allows visitors to post comments to any static page.
* Third-party integrations - for example with Yelp or TripAdvisor for displaying extra information on a static page.

Snippets are displayed in the sidebar list on the Static Pages and can be added into a rich editor with a mouse click. Snippets are configurable and have properties that users can manage with the Inspector.

![image](https://raw.githubusercontent.com/rainlab/pages-plugin/master/docs/images/snippets-backend.png)

![image](https://raw.githubusercontent.com/rainlab/pages-plugin/master/docs/images/snippets-frontend.png)

## Managing menus

You can manage menus on the Menus tab of the Static Pages plugin. A website can contain multiple menus, for example the main menu, footer menu, sidebar menu, etc. A theme developer can include menus on a page layout with the `staticMenu` component.

Menus have two required properties - the menu **Name** and menu **Code**. The menu name is displayed in the menu list in the back-end. The menu code is required for referring menus in the layout code, it's the API parameter.

![image](https://raw.githubusercontent.com/rainlab/pages-plugin/master/docs/images/menu-management.png) {.img-responsive .frame}

Menus can contain multiple **menu items**, and menu items can be nested. Each menu item has a number of properties. There are properties common for all menu item types, and some properties depend on the item type. The common menu item properties are **Title** and **Type**. The Title defines the menu item text. The Type is a drop-down list which displays all menu item types available in your OctoberCMS copy.

![image](https://raw.githubusercontent.com/rainlab/pages-plugin/master/docs/images/menu-item.png) {.img-responsive .frame}

#### Standard menu item types
The available menu item types depend on the installed plugins, but there are three basic item types that are supported out of the box.

###### Header {.subheader}
Items of this type are used for displaying text and don't link to anything. The text could be used as a category heading for other menu items. This type will only show a title property.

###### URL {.subheader}
Items of this type are links to a specific fixed URL. That could be an URL of an or internal page. Items of this type don't have any other properties - just the title and URL.

###### Static Page {.subheader}
Items of this type refer to static pages. The static page should be selected in the **Reference** drop-down list described below.

###### All Static Pages {.subheader}
Items of this type expand to create links to all static pages defined in the theme. Nested pages are represented with nested menu items.

#### Custom menu item types
Other plugins can supply new menu item types. For example, the [Blog plugin](https://octobercms.com/plugin/rainlab-blog) by [RainLab](https://octobercms.com/author/RainLab) supplies two more types:

###### Blog Category {.subheader}
An item of this type represents a link to a specific blog category. The category should be selected in the **Reference** drop-down. This menu type also requires selecting a **CMS page** that outputs a blog category.

###### All Blog Categories {.subheader}
An item of this time expands into multiple items representing all blog existing categories. This menu type also requires selecting a **CMS page**.

#### Menu item properties
Depending on the selected menu item time you might need to provide other properties of the menu item. The available properties are described below.

###### Reference {.subheader}
A drop-down list of objects the menu item should refer to. The list content depends on the menu item type. For the **Static page** item type the list displays all static pages defined in the system. For the **Blog category** item type the list displays a list of blog categories.

###### Allow nested items {.subheader}
This checkbox is available only for menu item types that suppose nested objects. For example, static pages are hierarchical, and this property is available for the **Static page** item type. On the other hand, blog categories are not hierarchical, and the checkbox is hidden.

###### Replace this item with its generated children {.subheader}
A checkbox determining whether the menu item should be replaced with generated menu items. This property is available only for menu item types that suppose automatic item generating, for example for the **Static page** menu item type. The **Blog category** menu item type doesn't have this property because blog categories cannot be nested and menu items of this type always point to a specific blog category. This property is very handy when you want to include generated menu items to the root of the menu. For example, you can create the **All blog categories** menu item and enable the replacing. As a result you will get a menu that lists all blog categories on the first level of the menu. If you didn't enable the replacing, there would be a root menu item, with blog categories listed under it.

###### CMS Page {.subheader}
This drop-down is available for menu item types that require a special CMS page to refer to. For example, the **Blog category** menu item type requires a CMS page that hosts the `blogPosts` component. The CMS Page drop-down for this item type will only display pages that include this component.

###### Code {.subheader}
The Code field allows to assign the API code that you can use to set the active menu item explicitly in the page's `onInit()` handler described in the documentation.

## See also

Read the [Getting started with Static Pages](https://octobercms.com/blog/post/getting-started-static-pages) tutorial in the Blog.

Read the [documentation](/docs/documentation.md).
