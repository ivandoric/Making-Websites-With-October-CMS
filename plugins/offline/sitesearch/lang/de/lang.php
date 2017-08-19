<?php return [
    'plugin'            => [
        'name'                       => 'SiteSearch',
        'description'                => 'Globale Suchfunktion für dein Frontend',
        'author'                     => 'OFFLINE GmbH',
        'manage_settings'            => 'Konfigurieren Sie Ihre Suchfunktion',
        'manage_settings_permission' => 'Kann Einstellungen verwalten',
    ],
    'settings'          => [
        'mark_results'               => 'Markiere Treffer in Suchergebnissen',
        'mark_results_comment'       => 'Suchbegriff wird mit <mark> Tags umschlossen',
        'excerpt_length'             => 'Länge des Textauszuges',
        'excerpt_length_comment'     => 'Länge des Textauszuges, der in den Suchresultaten angezeigt wird',
        'use_this_provider'          => 'Diesen Provider verwenden',
        'use_this_provider_comment'  => 'Aktivieren, um Suchergebnisse von diesem Provider miteinzubeziehen',
        'provider_badge'             => 'Provider-Badge',
        'provider_badge_comment'     => 'Dieser Text wird neben jedem Suchresultat angezeigt.',
        'blog_posturl'               => 'URL der Blog-Post Seite',
        'blog_posturl_comment'       => 'Gib nur den statischen Teil der URL ein, keine dynamischen Parameter',
        'blog_page'                  => 'Blog-Post Seite',
        'blog_page_comment'          => 'Wähle die Seite aus, auf der dein Blog-Post angezeigt wird.',
        'album_page'                 => 'Album-Seite',
        'album_page_comment'         => 'Wähle die Seite aus, auf der dein Album angezeigt wird.',
        'photo_page'                 => 'Photo-Seite',
        'photo_page_comment'         => 'Wähle die Seite aus, auf der dein Photo angezeigt wird.',
        'portfolio_itemurl'          => 'URL der Portfolio Detail-Seite',
        'portfolio_itemurl_comment'  => 'Gib nur den statischen Teil der URL ein, keine dynamischen Parameter',
        'brands_itemurl'             => 'URL der Brands Detail-Seite',
        'brands_itemurl_comment'     => 'Gib nur den statischen Teil der URL ein, keine dynamischen Parameter',
        'showcase_itemurl'           => 'URL der Shpwcase Detail-Seite',
        'showcase_itemurl_comment'   => 'Gib nur den statischen Teil der URL ein, keine dynamischen Parameter',
        'octoshop_itemurl'           => 'Url der Produkt Detail-Seite',
        'octoshop_itemurl_comment'   => 'Gib nur den statischen Teil der URL ein, keine dynamischen Parameter',
        'octoshop_itemurl_badge'     => 'Produkt',
        'snipcartshop_itemurl_badge' => 'Produkt',
        'jkshop_itemurl'             => 'Url der Produkt Detail-Seite',
        'jkshop_itemurl_comment'     => 'Gib nur den statischen Teil der URL ein, keine dynamischen Parameter',
        'jkshop_itemurl_badge'       => 'Produkt',
        'experimental'               => 'Experimentelle Funktion:',
        'experimental_refer_to_docs' => 'Dieser Provider ist experimentell! Bitte lies <a target="_blank"
href="http://octobercms.com/plugin/offline-sitesearch#documentation">die Dokumentation</a>, bevor du ihn benutzt!',
    ],
    'searchResults'     => [
        'title'       => 'Suchresultate',
        'description' => 'Listet Suchresultate auf',
        'properties'  => [
            'no_results'       => [
                'title'       => '«Nichts gefunden» Text',
                'description' => 'Was angezeigt werden soll, wenn nichts gefunden wird',
            ],
            'provider_badge'   => [
                'title'       => 'Provider-Label anzeigen',
                'description' => 'Ob der Name des jeweiligen Suchproviders neben einem Resultat angezeigt werden soll',
            ],
            'results_per_page' => [
                'title' => 'Treffer pro Seite',
            ],
            'visit_page'       => [
                'title'       => '«Treffer anzeigen» Text',
                'description' => 'Dieser Text wird unterhalb jedes Suchresultates angezeigt',
            ],
        ],
    ],
    'searchInput'       => [
        'title'       => 'Suchfeld',
        'description' => 'Zeigt ein Suchfeld an',
        'properties'  => [
            'use_auto_complete' => [
                'title' => 'Suche während der Eingabe',
            ],
            'auto_complete_result_count' => [
                'title' => 'Max. Anzahl Sofort-Resultate',
            ],
            'search_page' => [
                'title' => 'Seite für Suchresultate',
                'description' => 'Die Suchanfrage wird an diese Seite versendet.',
                'null_value' => '-- Nicht verlinkt',
            ],
        ],
    ],
    'siteSearchInclude' => [
        'title'       => 'In SiteSearch beachten',
        'description' => 'Zu einer CMS Seite hinzufügen, um diese bei der Suche zu berücksichtigen',
    ],
];