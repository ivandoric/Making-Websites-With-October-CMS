<?php return [
    'plugin' => [
        'name' => 'SiteSearch',
        'description' => '在前端添加全局搜素',
        'author' => 'OFFLINE LLC',
        'manage_settings' => '管理 SiteSearch 配置',
        'manage_settings_permission' => '可管理 SiteSearch 的设置',
    ],
    'settings' => [
        'mark_results' => '在搜索结果中标记符合项',
        'mark_results_comment' => '在搜索项两端添加<mark>标签',
        'excerpt_length' => '摘录的长度',
        'excerpt_length_comment' => '在搜索结果列表中摘录的长度.',
        'use_this_provider' => '使用这个数据提供者',
        'use_this_provider_comment' => '启用该数据提供者的显示结果',
        'provider_badge' => '数据提供者标记',
        'provider_badge_comment' => '在一次搜索结果的标记位中显示的文本',
        'blog_posturl' => '博客帖子页面的链接',
        'blog_posturl_comment' => '只指定URL的固定部分，没有任何动态参数',
        'blog_page' => '博客帖子页面',
        'blog_page_comment' => '选择一个用来显示单个博客帖子的页面。需要生成这些帖子的URL',
        'album_page' => '相册页',
        'album_page_comment' => '选择一个用来显示相册的页面。需要为相册生成URL',
        'photo_page' => '相册页',
        'photo_page_comment' => '选择一个用来显示单个照片的页面。需要形成照片的URL',
        'news_page' => '新闻帖子页',
        'news_page_comment' => '选择一个用来显示单个新闻帖子的页面。需要形成新闻URL',
        'portfolio_itemurl' => 'portfolio详情页面的Url',
        'portfolio_itemurl_comment' => '只指定URL的固定部分，不包含任何动态参数',
        'brands_itemurl' => '品牌详情页面Url',
        'brands_itemurl_comment' => '只指定URL的固定部分，不包含任何动态参数',
        'showcase_itemurl' => '展示详情页面Url',
        'showcase_itemurl_comment' => '只指定URL的固定部分，不包含任何动态参数',
        'octoshop_itemurl' => '产品详情页面的Url',
        'octoshop_itemurl_comment' => '只指定URL的固定部分，不包含任何动态参数',
        'octoshop_itemurl_badge' => '产品',
        'snipcartshop_itemurl_badge' => '产品',
        'jkshop_itemurl' => '产品详情页的url',
        'jkshop_itemurl_comment' => '只指定URL的固定部分，不包含任何动态参数',
        'jkshop_itemurl_badge' => '产品',
        'experimental' => '试验特点:',
        'experimental_refer_to_docs' => '该数据提供者是试验性的!使用前请参考 <a target="_blank"
href="http://octobercms.com/plugin/offline-sitesearch#documentation">文件</a> .',
    ],
    'searchResults' => [
        'title' => '搜索结果',
        'description' => '显示一个搜索结果的列表',
        'properties' => [
            'no_results' => [
                'title' => '搜索不到您想要的信息',
                'description' => '如果没有结果，你想要显示的内容',
            ],
            'provider_badge' => [
                'title' => '显示数据提供者标记',
                'description' => '显示每个结果的数据提供者',
            ],
            'results_per_page' => [
                'title' => '每页搜索结果',
            ],
            'visit_page' => [
                'title' => '访问页面标签',
                'description' => '此链接文本置于每个结果下方',
            ],
        ],
    ],
    'searchInput' => [
        'title' => 'searchInput',
        'description' => '显示一个搜索框',
        'properties' => [
            'use_auto_complete' => [
                'title' => '输入的时候同时搜索',
            ],
            'auto_complete_result_count' => [
                'title' => '最大数量的自动搜索结果',
            ],
            'search_page' => [
                'title' => '搜索结果页面',
                'description' => '您的搜索查询将被发送到这个页面',
                'null_value' => '-- 不要显示任何链接',
            ],
        ],
    ],
    'siteSearchInclude' => [
        'title' => 'Include in SiteSearch',
        'description' => '把这个包含在CMS页面中，使得该页面能被搜索',
    ],
];