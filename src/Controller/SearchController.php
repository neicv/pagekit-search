<?php

namespace Friendlyit\Search\Controller;

use Pagekit\Application as App;

/**
 * @Access(admin=true)
 */
class SearchController 
{
    /**
     * 
     */
    public function indexAction()
    {
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
                'name'  => 'friendlyit/search:views/admin/settings.php'
            ],
            '$data' => [
                'config' => App::module('friendlyit/search')->config()
            ]
        ];
    }
}
