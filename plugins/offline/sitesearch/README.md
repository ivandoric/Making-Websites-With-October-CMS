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
* [Graker.PhotoAlbums](https://octobercms.com/plugin/graker-photoalbums)
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

Alternatively you can also use the `searchInput` component described below to generate this form
for you.

##### Search results

Create a page to display your search results. Add the `searchResults` component to it.
Use the `searchResults.query` parameter to display the user's search query.

```html
title = "Search results"
url = "/search"
layout = "default"

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


#### Modify the query before searching

If you want to modify the user's search query before the search is executed you can call the `forceQuery` method on the `searchResults` component from your page's `onStart` method.

```php
[searchResults]
resultsPerPage = 10
showProviderBadge = 1
noResultsMessage = "Your search returned no results."
visitPageMessage = "Visit page"
==
function onStart()
{
    $query = Request::get('q');
    $query = str_replace('ё', 'e', $query);
    $this->page->components['searchResults']->forceQuery($query);
}
==
{% component 'searchResults' %}
```

#### Change the results collection before displaying 

You can listen for the `offline.sitesearch.results` event and modify the query as you wish.

This is useful to remove certain results or change the sort order.

```php
[searchResults]
resultsPerPage = 10
showProviderBadge = 1
noResultsMessage = "Your search returned no results."
visitPageMessage = "Visit page"
==
function onInit()
{
    \Event::listen('offline.sitesearch.results', function ($results) {
        // return $results->filter(...);
        return $results->sortByDesc('model.custom_attribute');
    });
}
==
{% component 'searchResults' %}
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


### searchInput

Place this component anywhere you want to display a simple search input with "search as you type" capabilities.

#### Usage example

Add the `searchInput` component to any layout, partial or page.

```html
title = "Home"
url = "/"
...

[searchInput]
useAutoComplete = 1
autoCompleteResultCount = 5
showProviderBadge = 1
searchPage = "search.htm"
==
{% component 'searchInput' %}
```

##### Example css to style the component

```css
.ss-search-form {
    position: relative;
}
.ss-search-form__results {
    display: none;
    position: absolute;
    left: 0;
    top: 35px;
    width: 100%;
    background: #fff;
    padding: 1em;
    box-shadow: 0 2px 4px rgba(0, 0, 0, .1);
}
.ss-search-form__results--visible {
    display: block;
}
```

#### Properties

The following properties are available to change the component's behaviour.

##### useAutoComplete

If this property is enabled, a search query will be executed as soon as the user begins to type.

##### autoCompleteResultCount

This many results will be displayed to the user below the input field. There will be a 
"Show all results" link the user can click that takes her to a full search results page if one has
been specified via the `searchPage` property.

##### showProviderBadge

The search works by querying multiple providers (Pages, Blog, or other). If this option is enabled
each search result is marked with a badge to show which provider returned the result.

This is useful if your site has many different entities (ex. teams, employees, pages, blog entries).

##### searchPage

The filename of the page where you have placed a `searchResults` component. If a user clicks on the "Show all 
results" link it will take him to this page where a full search is run using the `searchResults` component.

## Add support for custom plugin contents

### Simple method

To return search results for you own custom plugin, register an event listener for the `offline.sitesearch.query`
event in your plugin's boot method.

Return an array containing a `provider` string and `results` array. Each result must provide at least a `title` key.  

#### Example to search for custom `documents`

```php
public function boot()
{
    \Event::listen('offline.sitesearch.query', function ($query) {

        // The controller is used to generate page URLs.
        $controller = \Cms\Classes\Controller::getController() ?? new \Cms\Classes\Controller();

        // Search your plugin's contents
        $items = YourCustomDocumentModel
            ::where('title', 'like', "%${query}%")
            ->orWhere('content', 'like', "%${query}%")
            ->get();

        // Now build a results array
        $results = $items->map(function ($item) use ($query, $controller) {

            // If the query is found in the title, set a relevance of 2
            $relevance = mb_stripos($item->title, $query) !== false ? 2 : 1;
            
            // Optional: Add an age penalty to older results. This makes sure that
            // newer results are listed first.
            // if ($relevance > 1 && $item->created_at) {
            //    $ageInDays = $item->created_at->diffInDays(\Illuminate\Support\Carbon::now());
            //    $relevance -= \OFFLINE\SiteSearch\Classes\Providers\ResultsProvider::agePenaltyForDays($ageInDays);
            // }

            return [
                'title'     => $item->title,
                'text'      => $item->content,
                'url'       => $controller->pageUrl('cms-page-file-name', ['slug' => $item->slug]),
                'thumb'     => optional($item->images)->first(), // Instance of System\Models\File
                'relevance' => $relevance, // higher relevance results in a higher
                                           // position in the results listing
                // 'meta' => 'data',       // optional, any other information you want
                                           // to associate with this result
                // 'model' => $item,       // optional, pass along the original model
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

### Advanced method

If you need a bit more flexibility you can also create your own `ResultsProvider` class. Simply extend SiteSearch's 
`ResultProvider` and implement the needed methods. Have a look at the existing providers shipped by this plugin to get
an idea of all the possibilities.

When your own `ResultsProvider` class is ready, register an event listener for the `offline.sitesearch.extend`
event in your plugin's boot method. There you can return one `ResultsProvider` (or multiple in an array) which will
be included every time a user runs a search on your website.  

#### Advanced example to search for custom `documents`

```php
public function boot()
{
    Event::listen('offline.sitesearch.extend', function () {
        return new DocumentsSearchProvider();
        
        // or
        // return [new DocumentsSearchProvider(), new FilesSearchProvider()]; 
    });
}
```

```php
<?php
use OFFLINE\SiteSearch\Classes\Providers\ResultsProvider;

class DocumentsSearchProvider extends ResultsProvider
{
    public function search()
    {
        // Get your matching models
        $matching = YourCustomDocumentModel::where('title', 'like', "%{$this->query}%")
                                           ->orWhere('content', 'like', "%{$this->query}%")
                                           ->get();

        // Create a new Result for every match
        foreach ($matching as $match) {
            $result            = $this->newResult();

            $result->relevance = 1;
            $result->title     = $match->title;
            $result->text      = $match->description;
            $result->url       = $match->url;
            $result->thumb     = $match->image;
            $result->model     = $match;
            $result->meta      = [
                'some_data' => $match->some_other_property,
            ];

            // Add the results to the results collection
            $this->addResult($result);
        }

        return $this;
    }

    public function displayName()
    {
        return 'My Result';
    }

    public function identifier()
    {
        return 'VendorName.PluginName';
    }
}
```

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

This feature works best with simple pages that include components, but don't rely on url parametres or other
variables (like a page number). CMS pages with dynamic URLs (like `/page/:slug`) won't be linked correctly from the search results listing.

If you have CMS pages with more complex dynamic contents consider writing your own search provider (see `Add support for custom
plugin contents`)


## Overwrite default markup

To overwrite the default markup copy all files from `plugins/offline/sitesearch/components/searchresults` to
`themes/your-theme/partials/searchResults` and modify them as needed.

If you gave an alias to the `searchResults` component make sure to put the markup in the appropriate partials directory `themes/your-theme/partials/your-given-alias`.
