<?php return [
    'plugin'            => [
        'name'                       => 'SiteSearch',
        'description'                => 'Global search for your frontend',
        'author'                     => 'OFFLINE LLC',
        'manage_settings'            => 'Manage SiteSearch settings',
        'manage_settings_permission' => 'Can manage SiteSearch settings',
    ],
    'settings'          => [
        'mark_results'               => 'Mark matches in search results',
        'mark_results_comment'       => 'Wrap the search term in <mark> tags',
        'excerpt_length'             => 'Excerpt length',
        'excerpt_length_comment'     => 'Length of the excerpt shown in the search results listing.',
        'use_this_provider'          => 'Use this provider',
        'use_this_provider_comment'  => 'Enable to display results for this provider',
        'provider_badge'             => 'Provider badge',
        'provider_badge_comment'     => 'Text to display in a search result\'s badge',
        'blog_posturl'               => 'Url of blog post page',
        'blog_posturl_comment'       => 'Only specify the fixed part of the URL without any dynamic parameters',
        'blog_page'                  => 'Blog post page',
        'blog_page_comment'          => 'Select a page used to display a single blog post. Needed to form URL for posts.',
        'album_page'                 => 'Album page',
        'album_page_comment'         => 'Select a page used to display a photo album. Needed to form URL for albums.',
        'photo_page'                 => 'Photo page',
        'photo_page_comment'         => 'Select a page used to display a single photo. Needed to form URL for photos.',
        'news_page'                  => 'News post page',
        'news_page_comment'          => 'Select a page used to display a single news post. Needed to form URL for news.',
        'portfolio_itemurl'          => 'Url of portfolio detail page',
        'portfolio_itemurl_comment'  => 'Only specify the fixed part of the URL without any dynamic parameters',
        'brands_itemurl'             => 'Url of brand detail page',
        'brands_itemurl_comment'     => 'Only specify the fixed part of the URL without any dynamic parameters',
        'showcase_itemurl'           => 'Url of showcase detail page',
        'showcase_itemurl_comment'   => 'Only specify the fixed part of the URL without any dynamic parameters',
        'octoshop_itemurl'           => 'Url of product detail page',
        'octoshop_itemurl_comment'   => 'Only specify the fixed part of the URL without any dynamic parameters',
        'octoshop_itemurl_badge'     => 'Product',
        'snipcartshop_itemurl_badge' => 'Product',
        'jkshop_itemurl'             => 'Url of product detail page',
        'jkshop_itemurl_comment'     => 'Only specify the fixed part of the URL without any dynamic parameters',
        'jkshop_itemurl_badge'       => 'Product',
        'experimental'               => 'Experimental feature:',
        'experimental_refer_to_docs' => 'This provider is experimental! Please refer to <a target="_blank"
href="http://octobercms.com/plugin/offline-sitesearch#documentation">the documentation</a> before using it.',
    ],
    'searchResults'     => [
        'title'       => 'Search results',
        'description' => 'Displays a list of search results',
        'properties'  => [
            'no_results'       => [
                'title'       => 'No results message',
                'description' => 'What to display, if there are no results returned',
            ],
            'provider_badge'   => [
                'title'       => 'Show provider badge',
                'description' => 'Display the name of the search provider for each result',
            ],
            'results_per_page' => [
                'title' => 'Results per page',
            ],
            'visit_page'       => [
                'title'       => 'Visit page label',
                'description' => 'This link text is placed below each result',
            ],
        ],
    ],
    'searchInput'     => [
        'title'       => 'Search input',
        'description' => 'Displays a search input',
        'properties'  => [
            'use_auto_complete' => [
                'title' => 'Search while typing',
            ],
            'auto_complete_result_count' => [
                'title' => 'Max. autocomplete results',
            ],
            'search_page' => [
                'title' => 'Page for search results',
                'description' => 'Your search query will be sent to this page.',
                'null_value' => '-- Don\'t display a link',
            ],
        ],
    ],
    'siteSearchInclude' => [
        'title'       => 'Include in SiteSearch',
        'description' => 'Add this to a CMS page to include it in the search results',
    ],
];
