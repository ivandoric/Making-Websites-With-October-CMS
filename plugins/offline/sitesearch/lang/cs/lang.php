<?php return [
    'plugin' => [
        'name' => 'SiteSearch',
        'description' => 'Globální vyhledávání pro váš frontend',
        'author' => 'OFFLINE LLC',
        'manage_settings' => 'Správa SiteSearch nastavení',
        'manage_settings_permission' => 'Možnost měnit SiteSearch nastavení',
    ],
    'settings' => [
        'mark_results' => 'Označit hledanou frázi ve výsledcích vyhledávání',
        'mark_results_comment' => 'Obalit vyhledávaný výraz tagem <mark>',
        'excerpt_length' => 'Délka ústižku',
        'excerpt_length_comment' => 'Délka ústřižku výsledku vyhledávání.',
        'use_this_provider' => 'Použít tuto službu',
        'use_this_provider_comment' => 'Povolit zobrazení výsledků vyhledávání z této služby',
        'provider_badge' => 'Označení služby',
        'provider_badge_comment' => 'Textové označení služby které se zobrazí vedle výsledku vyhledávání',
        'blog_posturl' => 'URL detailu blogového článku',
        'blog_posturl_comment' => 'Zadejte pevnou část URL bez dynamických parametrů, jako je konkrétní URL článku, nebo stránkování',
        'portfolio_itemurl' => 'Url of portfolio detail page',
        'portfolio_itemurl_comment' => 'Zadejte pevnou část URL bez dynamických parametrů, jako je konkrétní URL článku, nebo stránkování',
        'brands_itemurl' => 'URL detailní stránky značky',
        'brands_itemurl_comment' => 'Zadejte pevnou část URL bez dynamických parametrů, jako je konkrétní URL článku, nebo stránkování',
        'octoshop_itemurl' => 'Url der Produkt Detail-Seite',
        'octoshop_itemurl_comment' => 'Gib nur den statischen Teil der URL ein, keine dynamischen Parameter',
        'octoshop_itemurl_badge' => 'Produkt',
        'snipcartshop_itemurl_badge' => 'Produkt',
        'experimental' => 'Experimentalní funkce:',
        'experimental_refer_to_docs' => 'Tato služba je zatím v testování! Před použitím doporučujeme přečíst <a target="_blank" href="http://octobercms.com/plugin/offline-sitesearch#documentation">stránku dokumentace</a>.',
    ],
    'searchResults' => [
        'title' => 'Výsledky vyhledávání',
        'description' => 'Zobrazí seznam výsledků vyhledávání',
        'properties' => [
            'no_results' => [
                'title' => 'Žádný výsledek vyhledávání',
                'description' => 'Tento text se zobrazí, když se nepovede nic najít',
            ],
            'provider_badge' => [
                'title' => 'Zobrazit označení služby',
                'description' => 'Vedle každého výsledku vyhledávání zobrazí označení služby (jestli je to článek, nebo stránka, atd)',
            ],
            'results_per_page' => [
                'title' => 'Výsledků na stránku',
            ],
            'visit_page' => [
                'title' => 'Text odkazu pro detail',
                'description' => 'Text odkazu který se zobrazí pod každým výsledkem vyhledávání',
            ],
        ],
    ],
    'siteSearchInclude' => [
        'title' => 'Zahrnout do SiteSearch',
        'description' => 'Přidejte toto do CMS stránky aby byla zahrnutá do SiteSearch vyhledávání',
    ],
];