<?php
return [

    /*
    |--------------------------------------------------------------------------
    | Categories List
    |--------------------------------------------------------------------------
    |
    | Used for BlogController@categoryIndex()
    | This does not restrict when admins update the article
    */

    'categories' => [
        'news' => [
            'name' => 'お知らせ',
            'visible' => true,
        ],
        'general' => [
            'name' => '文実全体',
            'visible' => true,
        ],
        'workTeam' => [
            'name' => '分科局',
            'visible' => true,
        ],
        'exh' => [
            'name' => '展示団体',
            'visible' => true,
        ],
        'contrib' => [
            'name' => '個人･寄稿',
            'visible' => true,
        ],
        'make-az-one' => [
            'name' => 'make az one',
            'visible' => true
        ],
        'internal' => [
            'name' => '内部生向け',
            'visible' => false
        ],
        'update' => [
            'name' => '更新情報',
            'visible' => true
        ]
    ]
];
