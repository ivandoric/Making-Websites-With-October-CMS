# SiteSearch Plugin for OctoberCMS

This plugin adds global search capabilities to OctoberCMS.

## Available languages

* English
* German
* Czech
* Russian
* Persian (Farsi)
* Portuguese

You can translate all contents into your own language.

## Currently supported content types

* [RainLab.Pages](https://octobercms.com/plugin/rainlab-pages)
* [RainLab.Blog](https://octobercms.com/plugin/rainlab-blog)
* [Indikator.News](https://github.com/gergo85/oc-news)
* [Feegleweb.Octoshop](https://octobercms.com/plugin/feegleweb-octoshop)
* [Jiri.JKShop](http://octobercms.com/plugin/jiri-jkshop)
* [RadiantWeb.ProBlog](https://octobercms.com/plugin/radiantweb-problog)
* [Arrizalamin.Portfolio](https://octobercms.com/plugin/arrizalamin-portfolio)
* [Responsiv.Showcase](https://octobercms.com/plugin/responsiv-showcase)
* [VojtaSvoboda.Brands](https://octobercms.com/plugin/vojtasvoboda-brands)
* Native CMS pages (experimental)

**Multilingual contents via RainLab.Translate are supported.**

Support for more plugins is added upon request.

**You can easily extend this plugin to search your custom plugin's contents as well.
See the documentation for further information.**

### Get native support for your plugin

If you are a plugin developer and wish to have native support for your contents in SiteSearch please submit a pull
request for your search provider or send us a copy of you plugin so we can create the provider for you.

We cannot add support for every plugin but will add any plugin that has a notable project count on the October
Marketplace.


## Components

### searchResults

Place this component on your page to display search results.

#### Usage example

Create a search form that sends a query to your search page:

##### Search form

```html
<form action="{{ 'search' | page }}" method="get">
    <input name="q" type="text" placeholder="What are you looking for?" autocomplete="off">
    <button type="submit">Search</button>
</form>
```

**Important**: Use the `q` parameter to send the user's query.

##### Search results

Create a page to display your search results. Add the `searchResults` component to it.
Use the `searchResults.query` parameter to display the user's search query.

```html
title = "Search results"
url = "/search"
...

[searchResults]
resultsPerPage = 10
showProviderBadge = 1
noResultsMessage = "Your search did not return any results."
visitPageMessage = "Visit page"
==
<h2>Search results for {{ searchResults.query }}</h2>

{% component 'searchResults' %}
```

##### Example css to style the component

```css
.ss-result {
    margin-bottom: 2em;
}
.ss-result__aside {
    float: right;
    margin-left: .5em;
}
.ss-result__title {
    font-weight: bold;
    margin-bottom: .5em;
}
.ss-result__badge {
    font-size: .7em;
    padding: .2em .5em;
    border-radius: 4px;
    margin-left: .75em;
    background: #eee;
    display: inline-block;
}
.ss-result__text {
    margin-bottom: .5em;
}
.ss-result__url {
}
```

#### Properties

The following properties are available to change the component's behaviour.

##### resultsPerPage

How many results to display on one page.

##### showProviderBadge

The search works by querying multiple providers (Pages, Blog, or other). If this option is enabled
each search result is marked with a badge to show which provider returned the result.

This is useful if your site has many different entities (ex. teams, employees, pages, blog entries).

##### noResultsMessage

This message is shown if there are no results returned.

##### visitPageMessage

A link is placed below each search result. Use this property to change that link's text.

## Add support for custom plugin contents

To return search results for you own custom plugin, register an event listener for the `offline.sitesearch.query`
event in your plugin's boot method.

Return an array containing a `provider` string and `results` array. Each result must provide at least a `title` key.  

### Example to search for custom `documents`

```php
public function boot()
{
    \Event::listen('offline.sitesearch.query', function ($query) {

        // Search your plugin's contents
        $items = YourCustomDocumentModel::where('title', 'like', "%${query}%")
                                        ->orWhere('content', 'like', "%${query}%")
                                        ->get();

        // Now build a results array
        $results = $items->map(function ($item) use ($query) {

            // If the query is found in the title, set a relevance of 2
            $relevance = mb_stripos($item->title, $query) !== false ? 2 : 1;

            return [
                'title'     => $item->title,
                'text'      => $item->content,
                'url'       => '/document/' . $item->slug,
                'thumb'     => $item->images->first(), // Instance of System\Models\File
                'relevance' => $relevance, // higher relevance results in a higher
                                           // position in the results listing
                // 'meta' => 'data',       // optional, any other information you want
                                           // to associate with this result
            ];
        });

        return [
            'provider' => 'Document', // The badge to display for this result
            'results'  => $results,
        ];
    });
}
```

That's it!

## Settings

You can manage all of this plugin's settings in the October CMS backend.

### Rainlab.Pages

No special configuration is required.

### Rainlab.Blog

Make sure you select your CMS page with the `blogPost` component as the `blog post page` in the backend settings.

You can access a post's published_at date in your search results via `{{ result.meta }}`.

### Feegleweb.Octoshop

Make sure you set the `Url of product detail page` setting to point to the right url. Only specify the fixed part of
the URL: `/product`. If your products are located under `/product/:slug` the default value is okay.

### Jiri.JKShop

Make sure you set the `Url of product detail page` setting to point to the right url. Only specify the fixed part of
the URL: `/product`. If your products are located under `/product/:slug` the default value is okay.

You can access an article's price in your search results via `{{ result.meta }}`.

### Indikator.News

Make sure you set the `News post page` setting to point to the right url. Only specify the fixed part of
the URL: `/news/post`. If your products are located under `/news/post/:slug` the default value is okay.

### RadiantWeb.ProBlog

Make sure you set the `Url of blog post page` setting to point to the right url. Only specify the fixed part of
the URL: `/blog`. If your posts are located under `/blog/:category/:slug` the default value is okay.

### ArrizalAmin.Portfolio

Make sure you set the `Url of portfolio detail page` setting to point to the right url. Only specify the fixed part of
the URL: `/portfolio/project`. If your detail page is located under `/portfolio/project/:slug` the default value is okay.

### VojtaSvoboda.Brands

Make sure you set the `Url of brand detail page` setting to point to the right URL. Only specify the fixed part of the URL: `/brand`. If your brand detail page is located under `/brand/:slug` then insert only `/brand` without the slug parameter.

### CMS pages (experimental)

If you want to provide search results for CMS pages change the `enabled` setting to `On`.

You have to specifically add the component `siteSearchInclude` to every CMS page you want to be searched.
Pages **without** this component will **not** be searched.

Components on CMS pages will **not** be rendered. Use this provider only for simple html pages. All Twig syntax will be stripped out to prevent the leaking of source code to the search results.

CMS pages with dynamic URLs (like `/page/:slug`) won't be linked correctly from the search results listing.

If you have CMS pages with dynamic contents consider writing your own search provider (see `Add support for custom
plugin contents`)


## Overwrite default markup

To overwrite the default markup copy all files from `plugins/offline/sitesearch/components/searchresults` to
`themes/your-theme/partials/searchResults` and modify them as needed.

If you gave an alias to the `searchResults` component make sure to put the markup in the appropriate partials directory `themes/your-theme/partials/your-given-alias`.

