<?php return [
    'plugin'            => [
        'name'                       => 'Sivusto Haku',
        'description'                => 'Sivuston kattava haku',
        'author'                     => 'OFFLINE LLC',
        'manage_settings'            => 'Hallinnoi Sivusto Haun asetuksia',
        'manage_settings_permission' => 'saa hallita Sivusto Haun asetuksia',
    ],
    'settings'          => [
        'mark_results'               => 'Merkitse osumat hakutuloksista',
        'mark_results_comment'       => 'Kääri hakutulokset <mark> tagiin',
        'excerpt_length'             => 'Poiminnan pituus',
        'excerpt_length_comment'     => 'Kuinka monta merkkiä poimitaan hakutuloksiin.',
        'use_this_provider'          => 'Käytä tätä toimittajaa',
        'use_this_provider_comment'  => 'Aktivoi tämän toimittajan haut',
        'provider_badge'             => 'Tarjoajan tunnus',
        'provider_badge_comment'     => 'Teksti, joka näytetään tarjoajan tunnuksena',
        'blog_posturl'               => 'Blogipostauksen URL-osoite',
        'blog_posturl_comment'       => 'Sisällytä vain URL-osoitteen kiinteä osa ilman dynaamisia parametreja',
        'blog_page'                  => 'Blogipostauksen sivu',
        'blog_page_comment'          => 'Valitse yksittäinen blogipostauksen sivu. Tarvitaan URL-osoitteen muodostamiseksi.',
        'album_page'                 => 'Albumisivu',
        'album_page_comment'         => 'Valitse yksittäinen valokuva-albumi. Tarvitaan URL-osoitteen muodostamiseksi.',
        'photo_page'                 => 'Valokuvasivu',
        'photo_page_comment'         => 'Valitse yksittäinen valokuvasivu. Tarvitaan URL-osoitteen muodostamiseksi.',
        'news_page'                  => 'Uutissivu',
        'news_page_comment'          => 'Valitse yksittäinen uutissivu. Tarvitaan URL-osoitteen muodostamiseksi.',
        'portfolio_itemurl'          => 'Portfoliosivun URL-osoite',
        'portfolio_itemurl_comment'  => 'Sisällytä vain URL-osoitteen kiinteä osa ilman dynaamisia parametreja',
        'brands_itemurl'             => 'Brändisivun URL-osoite',
        'brands_itemurl_comment'     => 'Sisällytä vain URL-osoitteen kiinteä osa ilman dynaamisia parametreja',
        'showcase_itemurl'           => 'Esittelysivun URL-osoite',
        'showcase_itemurl_comment'   => 'Sisällytä vain URL-osoitteen kiinteä osa ilman dynaamisia parametreja',
        'octoshop_itemurl'           => 'Tuotesivun URL-osoite',
        'octoshop_itemurl_comment'   => 'Sisällytä vain URL-osoitteen kiinteä osa ilman dynaamisia parametreja',
        'octoshop_itemurl_badge'     => 'Tuotetunnus',
        'snipcartshop_itemurl_badge' => 'Tuotetunnus',
        'jkshop_itemurl'             => 'Tuotesivun ULR-osoite',
        'jkshop_itemurl_comment'     => 'Sisällytä vain URL-osoitteen kiinteä osa ilman dynaamisia parametreja',
        'jkshop_itemurl_badge'       => 'Tuotetunnus',
        'experimental'               => 'Kokeellinen ominaisuus:',
        'experimental_refer_to_docs' => 'Tämä tarjoaja on kokeellinen! Ole hyvä ja tutustu <a target="_blank"
href="http://octobercms.com/plugin/offline-sitesearch#documentation">dokumentaatioon</a> ennen käyttöönottoa.',
    ],
    'searchResults'     => [
        'title'       => 'Hakutulokset',
        'description' => 'Näyttää listan hakutulokista',
        'properties'  => [
            'no_results'       => [
                'title'       => 'Ei tuloksia viesti',
                'description' => 'Mitä näytetään, kun hakutuloksia ei ole',
            ],
            'provider_badge'   => [
                'title'       => 'Näytä tarjoajatunnus',
                'description' => 'Näytä jokaisessa hakutuloksessa tuloksen tarjoaja',
            ],
            'results_per_page' => [
                'title' => 'Tuloksia per sivu',
            ],
            'visit_page'       => [
                'title'       => 'Vierailusivun lappu',
                'description' => 'Tämä teksti näytetään jokaisen hakutuloksen alapuolella',
            ],
        ],
    ],
    'searchInput'     => [
        'title'       => 'Hakukenttä',
        'description' => 'Näytä hakukenttä',
        'properties'  => [
            'use_auto_complete' => [
                'title' => 'Ennakoivahaku',
            ],
            'auto_complete_result_count' => [
                'title' => 'Maks. ennakoituja hakutuloksia',
            ],
            'search_page' => [
                'title' => 'Hakutulosten sivu',
                'description' => 'Hakutulos lähetetään tähän sivuun.',
                'null_value' => '-- Älä näytä linkkiä',
            ],
        ],
    ],
    'siteSearchInclude' => [
        'title'       => 'Sisällytä Sivusto Hakuun',
        'description' => 'Lisää tämä sivu hakutuloksiin',
    ],
];
