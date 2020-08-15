<?php return [
    'plugin'            => [
        'name'                       => 'SiteSearch',
        'description'                => 'Глобално търсене за вашия фронтенд',
        'author'                     => 'OFFLINE LLC',
        'manage_settings'            => 'Управление на SiteSearch настройките',
        'manage_settings_permission' => 'Може да управлява SiteSearch настройките',
    ],
    'settings'          => [
        'mark_results'               => 'Маркиране на съвпаденията в резултатите',
        'mark_results_comment'       => 'Отделяне на ключа за търсене в <mark> таг',
        'excerpt_length'             => 'Дължина на израза',
        'excerpt_length_comment'     => 'Дължина на израза показана в листа с резултатите.',
        'use_this_provider'          => 'Използвай този доставчик',
        'use_this_provider_comment'  => 'Включи показването на резултатите за този доставчик',
        'provider_badge'             => 'Бадж на доставчика',
        'provider_badge_comment'     => 'Текста на баджа, който ще се показва в намерените резултати.',
        'blog_posturl'               => 'Url на страницата на публикацията в блога',
        'blog_posturl_comment'       => 'Моля, отбележете само статучната част от URL-то без динамичните параметри',
        'blog_page'                  => 'Страница на публикацията в блога',
        'blog_page_comment'          => 'Изберете страницата използвана за показване на публикацията в блога. Необходима е за връзката към публикациите.',
        'album_page'                 => 'Album page', 'Страница на Album',
        'album_page_comment'         => 'Изберете страницата използвана за показване на фото албума. Необходима е за връзката към албумите.',
        'photo_page'                 => 'Photo page', 'Страница на Photo',
        'photo_page_comment'         => 'Изберете страницата за показване на единична фотография. Необходима е за връзката към фотографиите.',
        'news_page'                  => 'News post page', 'Страница за нова публикация',
        'news_page_comment'          => 'Изберете страницата използвана за показване на публикация. Необходима е за връзката към публикациите.',
        'portfolio_itemurl'          => 'Url of portfolio detail page', 'Url на портфолио страницата',
        'portfolio_itemurl_comment'  => 'Моля, отбележете само статучната част от URL-то без динамичните параметри',
        'brands_itemurl'             => 'Url of brand detail page','Url на Марка страницата',
        'brands_itemurl_comment'     => 'Моля, отбележете само статучната част от URL-то без динамичните параметри',
        'showcase_itemurl'           => 'Url на showcase страницата',
        'showcase_itemurl_comment'   => 'Моля, отбележете само статучната част от URL-то без динамичните параметри',
        'octoshop_itemurl'           => 'Url на страницата на продукта',
        'octoshop_itemurl_comment'   => 'Моля, отбележете само статучната част от URL-то без динамичните параметри',
        'octoshop_itemurl_badge'     => 'Продукт',
        'snipcartshop_itemurl_badge' => 'Продукт',
        'jkshop_itemurl'             => 'Url на страницата на продукта',
        'jkshop_itemurl_comment'     => 'Моля, отбележете само статучната част от URL-то без динамичните параметри',
        'jkshop_itemurl_badge'       => 'Продукт',
        'experimental'               => 'Експериментални възможности:',
        'experimental_refer_to_docs' => 'Този доставчик е експериментален! Моля, погледнете <a target="_blank"
href="http://octobercms.com/plugin/offline-sitesearch#documentation">документацията</a> преди да го използвате.',
    ],
    'searchResults'     => [
        'title'       => 'Резултати от търсенето',
        'description' => 'Показва списък с резултатите от търсенето.',
        'properties'  => [
            'no_results'       => [
                'title'       => 'Съобщение за липсващи резултати',
                'description' => 'Какво да се покаже, когато не са намерени резултати',
            ],
            'provider_badge'   => [
                'title'       => 'Покажи баджа на доставчика',
                'description' => 'Покажи името на доставчика за всеки резултат',
            ],
            'results_per_page' => [
                'title' => 'Резултати на страница',
            ],
            'visit_page'       => [
                'title'       => 'Етикет на връзката',
                'description' => 'Този етикет на връзката се показва под всеки резултат.',
            ],
        ],
    ],
    'searchInput'     => [
        'title'       => 'Поле за търсенето',
        'description' => 'Показва полето за търсене.',
        'properties'  => [
            'use_auto_complete' => [
                'title' => 'Търси докато пишеш',
            ],
            'auto_complete_result_count' => [
                'title' => 'Максимален брой автоматични резултати',
            ],
            'search_page' => [
                'title' => 'Страница за показване на резултатите',
                'description' => 'Резултатите от търсенето ще бъдат показани на тази страница.',
                'null_value' => '-- не показвай връзката',
            ],
        ],
    ],
    'siteSearchInclude' => [
        'title'       => 'Включено в SiteSearch',
        'description' => 'Добавете това към CMS страницата, за да я включите в търсенето',
    ],
];

