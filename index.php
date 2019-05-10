<?php
/**
 * @package     Pagekit Extension Search
 *
 * @copyright   Copyright (C) 2016-2019 Friendly-it, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

//namespace Pagekit\Site;
use Friendlyit\Search\Plugin\SearchContentPlugin;
use Friendlyit\Search\Plugin\SearchPagePlugin;
use Friendlyit\Search\Plugin\SearchWidgetTextPlugin;
use Friendlyit\Search\Plugin\SearchBlogPlugin;
use Friendlyit\Search\Plugin\SearchAdvBlogPlugin;
use Friendlyit\Search\Plugin\SearchDrivenListingsPlugin;
use Pagekit\Application as App;
//use Pagekit\Site\Model\Node;

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
            'controller' =>	'Friendlyit\\Search\\Controller\\SearchController'
        ],
		'/api/search' => [
            'name' => '@search/api',
            'controller' => 'Friendlyit\\Search\\Controller\\StatisticsApiController'
        ],
        '/search/info' => [
            'name' => '@search/info',
            'controller' => 'Friendlyit\\Search\\Controller\\InfoController'
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

			'limit_search_result'   => 50,
            'result_per_page'       => 10,
            'data_creation'         => true,
            'use_areas_search'      => true, 
            'markdown_enabled'      => true,
			'show_pages_counter'	=> true,
			'show_posted_in'	  	=> true,
			'title'            	  	=> 'Search Title',
            'show_title'            => true,
            'highlight'             => 'highlight',

        ],
		'advanced' => [

            'statistics_enabled'       => false,
        ],

        'pluginDrivenListings' => [

            'use_item_href'         => false,
            'use_items_area'        => true,
            'use_category_area'     => true,
            'use_listing_area'      => true,
            'use_sharp_links'       => false,
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
            'label' => 'Settings',
            'parent' => 'search',
            'url' => '@search/settings',
            'active' => '@search/settings*',
            'access' => 'system: manage settings'
        ],
        'search: info' => [
            'label' => 'Info',
            'parent' => 'search',
            'url' => '@search/info',
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
	
	'settings' => '@search/settings',

	'events' => [

/*        'boot' => function ($event, $app) {
            $app->subscribe(
                //new SearchContentPlugin,
                new SearchPagePlugin,
				new SearchBlogPlugin
                
            );
        },  */
		
		
		'boot' => function ($event, $app) {
			if (App::module('blog')) {

                $query =  App::db()->createQueryBuilder();
                $query = $query->from('@system_node');
                $query->where(['type' => 'blog']);
                $query->where(['status' => 1] );
                $count = $query->count();
                            
                //$query = Node::query();

                if ($count){
                    $app->subscribe(
                    new SearchBlogPlugin
                    );
                }
			}
			
			if (App::module('driven/listings')) {
                $app->subscribe(
                new SearchDrivenListingsPlugin
                );
            }
            
            if (App::module('dpnblog')) {

                $query =  App::db()->createQueryBuilder();
                $query = $query->from('@system_node');
                $query->where(['type' => 'dpnblog']);
                $query->where(['status' => 1] );
                $count = $query->count();

                if ($count){
                    $app->subscribe(
                    new SearchAdvBlogPlugin
                    );
                }
			}
			
			$app->subscribe(
                new SearchPagePlugin,
                new SearchWidgetTextPlugin
			);
        }, 
		
		
		
		'view.scripts' => function ($event, $scripts) use ($app) {
            $scripts->register('uikit-search', 'app/assets/uikit/js/components/search.min.js', 'uikit');
            $scripts->register('uikit-autocomplete', 'app/assets/uikit/js/components/autocomplete.min.js', 'uikit');
        }

    ]

];