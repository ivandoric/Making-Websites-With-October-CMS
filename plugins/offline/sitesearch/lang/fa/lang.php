<?php return [
    'plugin'            => [
        'name'                       => 'جستجو در سایت',
        'description'                => 'جستجوی جهانی توسعه دهنده رابط کاربری',
        'author'                     => 'Offline LLC',
        'manage_settings'            => 'مدیریت تنظیمات جستجوی سایت',
        'manage_settings_permission' => 'آیا می توانید تنظیمات جستجوی سایت را مدیریت کنید؟',
    ],
    'settings'          => [
        'mark_results'               => 'علامتگذاری به عنوان همخوانی داشتن در نتیجه جستجو',
        'mark_results_comment'       => 'قرار دادن عبارت جستجو در <علامت> برچسب ها',
        'excerpt_length'             => 'رشته منتخب',
        'excerpt_length_comment'     => 'نمایش رشته انتخابی در لیست نتایج جستجو',
        'use_this_provider'          => 'استفاده از این ارائه دهنده ها',
        'use_this_provider_comment'  => 'قادر به نمایش نتایج برای این ارائه دهنده',
        'provider_badge'             => 'نشان دهنده',
        'provider_badge_comment'     => 'متن جهت نمایش در نتیجه',
        'blog_posturl'               => 'آدرس وبلاگ صفحه پست',
        'blog_posturl_comment'       => 'تنها بخش ثابت از آدرس بدون هیچ پارامترهای پویا و مشخص است.',
        'blog_page'                  => 'صفحه پست وبلاگ',
        'blog_page_comment'          => 'صفحه مورد استفاده برای نمایش یک پست وبلاگ را انتخاب کنید.برای تشکیل آدرس برای پست مورد نیاز است.',
        'album_page'                 => 'صفحه آلبوم',
        'album_page_comment'         => 'صفحه ای را جهت نمایش آلبوم تصاویر انتخاب نمایید. این گزینه جهت تولید آدرس آلبوم ها مورد نیاز می باشد.',
        'photo_page'                 => 'صفحه تصویر',
        'photo_page_comment'         => 'صفحه ای را جهت نمایش یک تصویر انتخاب نمایید. این گزینه برای ایجاد آدرس تصویر مورد نیاز می باشد.',
        'news_page'                  => 'صفحه نمایش خبر',
        'news_page_comment'          => 'صفحه ای را جهت نمایش یک خبر انتخاب نمایید. این گزینه جهت تولید آدرس خبر مورد نیاز می باشد.',
        'portfolio_itemurl'          => 'آدرس صفحه جزئیات نمونه کارها',
        'portfolio_itemurl_comment'  => 'تنها بخش ثابت از آدرس بدون هیچ پارامترهای پویا و مشخص است.',
        'brands_itemurl'             => 'آدرس صفحه جزئیات نمونه کارها',
        'brands_itemurl_comment'     => 'تنها بخش ثابت از آدرس بدون هیچ پارامترهای پویا و مشخص است.',
        'showcase_itemurl'           => 'آدرس صفحه جزئیات ویترین',
        'showcase_itemurl_comment'   => 'تنها بخش ثابت از آدرس بدون هیچ پارامترهای پویا و مشخص است.',
        'octoshop_itemurl'           => 'آدرس صفحه جزئیات محصول',
        'octoshop_itemurl_comment'   => 'تنها بخش ثابت از آدرس بدون هیچ پارامترهای پویا و مشخص است.',
        'octoshop_itemurl_badge'     => 'محصول',
        'snipcartshop_itemurl_badge' => 'محصول',
        'jkshop_itemurl'             => 'آدرس محصول',
        'jkshop_itemurl_comment'     => 'Gib nur den statischen Teil der URL ein, keine dynamischen Parameter',
        'jkshop_itemurl_badge'       => 'محصولات',
        'experimental'               => 'ویژگی های تجربی:',
        'experimental_refer_to_docs' => 'این ارائه دهنده تجربی است. لطفا به <a target="_blank"
href="http://octobercms.com/plugin/offline-sitesearch#documentation">اسنادو مدارک</a>قبل از استفاده از آن است.',
    ],
    'searchResults'     => [
        'title'       => 'نتایج جستجو',
        'description' => 'نمایش یک لیست از نتایج جستجو',
        'properties'  => [
            'no_results'       => [
                'title'       => 'هیچ موردی یافت نشد.',
                'description' => 'متنی که مایلید در صورت نداشتن نتیجه نمایش داده شود',
            ],
            'provider_badge'   => [
                'title'       => 'نمایش ارائه دهنده',
                'description' => 'نمایش نام ارائه دهنده جستجو برای هر نتیجه',
            ],
            'results_per_page' => [
                'title' => 'نتایج در هر صفحه',
            ],
            'visit_page'       => [
                'title'       => 'برچسب صفحه مشاهده',
                'description' => 'این متن لینک قرار داده شده است زیر هر نتیجه',
            ],
        ],
    ],
    'searchInput'     => [
        'title'       => 'ورودی جستجو',
        'description' => 'نمایش یک ورودی جهت جستجو',
        'properties'  => [
            'use_auto_complete' => [
                'title' => 'جستجو بهنگام تایپ',
            ],
            'auto_complete_result_count' => [
                'title' => 'حداکثر مورد جهت نمایش در تکمیل خودکار',
            ],
            'search_page' => [
                'title' => 'صفحه نمایش نتایج جستجو',
                'description' => 'متن جستجو به این صفحه هدایت خواهد شد.',
                'null_value' => '-- آدرسی را نمایش نده',
            ],
        ],
    ],
    'siteSearchInclude' => [
        'title'       => 'شامل جستجو در سایت',
        'description' => 'به صفحات محتوایی اضافه کنید تا در نتیجه جستجوها نمایش داده شوند',
    ],
];
