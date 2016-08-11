<?php
/**
 * @package     Pagekit Extension
 * @subpackage  Search.content - Articles
 *
 * @copyright   Copyright (C) 2016 Friendly-it, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
 
namespace Friendlyit\Search\Plugin;

use Friendlyit\Search\Event\SearchEvent;
use Pagekit\Event\EventSubscriberInterface;

/**
 * Content search plugin.
 *
 */
 
 /**
 * Content plugins callback.
 *
 * @param SearchEvent $event
 */
 
class SearchContentPlugin implements EventSubscriberInterface
{
    /**
     * SearchContent plugins callback.
	 *
	 * Determine areas searchable by this plugin.
	 *
	 * @return  array  An array of search areas.
	 *
	 * @since   0.1
	 */
	 
	public function onContentSearchAreas(SearchEvent $event)
	{
		
		static $areas = array();
		$areas = ['content' => __('Articles') ]; 
		$event->setSearchArray($areas);
	}

	public function onContentSearchAreasL()
	{
		static $areas = array();
		$areas = ['content' => __('Articles') ]; 
		return $areas;
	}
	
	/**
	 * Search content (articles).
	 * The SQL must return the following fields that are used in a common display
	 * routine: href, title, section, created, text, browsernav.
	 *
	 * @param   string  $text      Target search string.
	 * @param   string  $phrase    Matching option (possible values: exact|any|all).  Default is "any".
	 * @param   string  $ordering  Ordering option (possible values: newest|oldest|popular|alpha|category).  Default is "newest".
	 * @param   mixed   $areas     An array if the search it to be restricted to areas or null to search all areas.
	 *
	 * @return  array  Search results.
	 *
	 * @since   0.1
	 */

	public function onContentSearch(SearchEvent $event)
	{ 
	  
		if (!$article = App::module('article')) {return array();}
		
		$params = App::module('friendlyit/search')->config('defaults');
		$limit 		= isset($params['limit_search_result']) ? $params['limit_search_result'] : self::PAGES_PER_PAGE;
		$markdown 	= isset($params['markdown_enabled']) ? $params['markdown_enabled'] : true ;
	  
		$parameters = $event->getParameters();
		
		$text 		= $parameters[0];
		$phrase 	= $parameters[1];
		$ordering	= $parameters[2];
		$areas  	= $parameters[3]; 
		
		if (is_array($areas))
		{
			if (!array_intersect($areas, array_keys($this->onContentSearchAreasL())))
			{
				$a = array();
				$event->setSearchData($a);
				return $a;
			}
		} 
		//$event->setSearchData($parameters);
		return 'PlgSearchContent';
	}

    /**
     * {@inheritdoc}
     */
    public function subscribe()
    {
        return [
			'search.onContentSearchAreas'	=> ['onContentSearchAreas', 5],
			'search.onContentSearch'		=> ['onContentSearch', 5]
        ];
    }
}