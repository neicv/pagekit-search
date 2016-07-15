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
            'name' => '@search/admin',
            'controller' => [
			'Friendlyit\\Search\\Controller\\SearchController'
			]
        ],
    ],
	
	'widgets' => [

        'widgets/searchwidget.php'

    ],
	
	
	'resources' => [

		'friendlyit/search:' => ''

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
    ],

    'menu' => [

        'search' => [
            'label'  => 'Search',
            'icon'   => 'friendlyit/search:icon.svg',
            'url'    => '@search/admin',
            'active' => '@search*',
            //'access' => 'search: see search'
        ],
		'search: panel' => [
            'parent' => 'search',
            'label' => 'Search',
            'icon' => 'friendlyit/search:icon.svg',
            'url' => '@search/admin',
            'access' => 'search: manage search'
        ],
        'search: settings' => [
            'parent' => 'search',
            'label' => 'Settings',
            'url' => '@search/admin/settings',
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

    ]

];