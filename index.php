<?php

use Friendlyit\Search\Plugin\SearchContentPlugin;
use Friendlyit\Search\Plugin\SearchPagePlugin;
use Friendlyit\Search\Plugin\SearchBlogPlugin;

return [

    'name' => 'friendlyit/search',

    'type' => 'extension',

    'autoload' => [

		'Friendlyit\\Search\\' => 'src'

    ],

	'nodes' => [

        'search' => [
			'name' => '@search',
            'label' => 'Search',
            'controller' => 'Friendlyit\\Search\\Controller\\SiteController',
            'protected' => true
        ]
    ],

	'routes' => [

        '/search' => [
            'name' => '@search',
            'controller' => [
			'Friendlyit\\Search\\Controller\\SearchController'
			]
        ],
		'/api/search' => [
            'name' => '@search/api',
            'controller' => [
                'Friendlyit\\Search\\Controller\\StatisticsApiController',
            ]
        ]
    ],
	
	'widgets' => [

        'widgets/searchwidget.php'

    ],
	
	
	'resources' => [

		'friendlyit/search:' => '',
		'views:friendlyit/search' => 'views'

	],

	
	'config' => [

        'defaults' => [

			'limit_search_result'    => 50,
            'result_per_page'        => 10,
            'data_creation'          => true,
            'use_areas_search'       => true, 
            'markdown_enabled'       => true,
			'show_pages_counter'	 => true,
			'show_posted_in'	  	 => true,
			'title'            	  	 => 'Search Title',
            'show_title'             => true

        ],
		'advanced' => [

            'statistics_enabled'       => false,
        ],
    ],

    'menu' => [

        'search' => [
            'label'  => 'Search',
            'icon'   => 'friendlyit/search:icon.svg',
            'url'    => '@search/statistics',
            'active' => '@search/statistics*',
            //'access' => 'search: see search'
        ],
		'search: statistics' => [
            'parent' => 'search',
            'label' => 'Statistics',
            'icon' => 'friendlyit/search:icon.svg',
            'url' => '@search/statistics',
            'access' => 'search: manage search'
        ],
        'search: settings' => [
            'parent' => 'search',
            'label' => 'Settings',
            'url' => '@search/settings',
            'access' => 'system: manage settings'
        ]
    ],

    'permissions' => [

        'search: see search' => [
            'title' => 'See search result'
        ],
		'search: manage settings' => [
            'title' => 'Manage settings'
        ],

    ],
	
	'settings' => '@search/admin/settings',
	 
	'events' => [

        'boot' => function ($event, $app) {
            $app->subscribe(
                //new SearchContentPlugin,
                new SearchPagePlugin,
				new SearchBlogPlugin
                
            );
        },
		'view.scripts' => function ($event, $scripts) use ($app) {
            $scripts->register('uikit-search', 'app/assets/uikit/js/components/search.min.js', 'uikit');
            $scripts->register('uikit-autocomplete', 'app/assets/uikit/js/components/autocomplete.min.js', 'uikit');
        }

    ]

];