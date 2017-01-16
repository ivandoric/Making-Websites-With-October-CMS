<?php return [
    'plugin'            => [
        'name'                       => 'SiteSearch',
        'description'                => 'Глобальный поиск для вашего frontend-а',
        'author'                     => 'OFFLINE LLC',
        'manage_settings'            => 'Управление настройками SiteSearch',
        'manage_settings_permission' => 'Разрешить управлять настройками SiteSearch',
    ],
    'settings'          => [
        'mark_results'               => 'Пометить совпадения в результатах поиска',
        'mark_results_comment'       => 'Обернуть искомый текст в тэги <mark>',
        'excerpt_length'             => 'Длина выборки текста',
        'excerpt_length_comment'     => 'Длина выборки текста, показываемого в результатах поиска.',
        'use_this_provider'          => 'Использовать этот провайдер',
        'use_this_provider_comment'  => 'Включить отображение результатов для этого провайдера',
        'provider_badge'             => 'Badge провайдера',
        'provider_badge_comment'     => 'Текст, отображаемый в Bage результата поиска',
        'blog_posturl'               => 'Url поста из блога',
        'blog_posturl_comment'       => 'Only specify the fixed part of the URL without any dynamic parameters',
        'blog_page'                  => 'Страница поста из блога',
        'blog_page_comment'          => 'Укажите страницу, используемую для вывода поста из блога. Это нужно для правильной генерации URL поста.',
        'portfolio_itemurl'          => 'Url of portfolio detail page',
        'portfolio_itemurl_comment'  => 'Only specify the fixed part of the URL without any dynamic parameters',
        'brands_itemurl'             => 'Url of brand detail page',
        'brands_itemurl_comment'     => 'Only specify the fixed part of the URL without any dynamic parameters',
        'octoshop_itemurl'           => 'Url of product detail page',
        'octoshop_itemurl_comment'   => 'Only specify the fixed part of the URL without any dynamic parameters',
        'octoshop_itemurl_badge'     => 'Product',
        'snipcartshop_itemurl_badge' => 'Product',
        'experimental'               => 'Эксперементальная фитча":',
        'experimental_refer_to_docs' => 'Этот провайдер является эксперементальным! Пожалуйста, обратитесь <a target="_blank"
href="http://octobercms.com/plugin/offline-sitesearch#documentation">к документации</a> перед использованием его.',
    ],
    'searchResults'     => [
        'title'       => 'Результаты поиска',
        'description' => 'Отображает список результатов поиска',
        'properties'  => [
            'no_results'       => [
                'title'       => 'Сообщение отсутсвия искомой информации',
                'description' => 'Что показывать, когда ничего не найдено',
            ],
            'provider_badge'   => [
                'title'       => 'Показывать bage провайдера',
                'description' => 'Отображает имя поискового провайдера для каждого результата',
            ],
            'results_per_page' => [
                'title' => 'Результатов на страницу',
            ],
            'visit_page'       => [
                'title'       => 'Метка Посетить страницу',
                'description' => 'Эта текстовая ссылка помещается под каждым результатом поиска',
            ],
        ],
    ],
    'siteSearchInclude' => [
        'title'       => 'Включить в SiteSearch',
        'description' => 'Добавьте это на страницу CMS, чтобы включить в результаты поиска',
    ],
];
