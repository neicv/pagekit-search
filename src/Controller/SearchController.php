<?php

namespace Friendlyit\Search\Controller;

use Pagekit\Application as App;

/**
 * @Access(admin=true)
 */
class SearchController 
{
    /**
     * -Response("search://views/admin/index.razr")
     */
    public function indexAction()
    {
        //return ['head.title' => __('Search')];
		return "Backend View";
    }
	
	/**
     * @Access("search: manage settings")
     */
    public function settingsAction()
    {
        return [
            '$view' => [
                'title' => __('Search Settings'),
                'name'  => 'search:views/admin/settings.php'
            ],
            '$data' => [
                'config' => App::module('search')->config()
            ]
        ];
    }
}
