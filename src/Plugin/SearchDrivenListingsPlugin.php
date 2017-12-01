<?php
/**
 * @package     Pagekit Extension
 * @subpackage  Search.content - Blog
 *
 * @copyright   Copyright (C) 2016 Friendly-it, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Friendlyit\Search\Plugin;

use Friendlyit\Search\Helpers\EXSearchHelper;
use Friendlyit\Search\Event\SearchEvent;

//use Pagekit\Blog\Model\Post;
//use Pagekit\Blog\Model\Comment;

use Pagekit\Component\Database\ORM\Repository;
use Pagekit\Event\EventSubscriberInterface;
use Pagekit\Application as App;

/**
 * Blog search plugin.
 *
 */

class SearchDrivenListingsPlugin implements EventSubscriberInterface
{
	const PAGES_PER_PAGE = 50;
	/**
     * @var Repository
     */
    //protected $posts;

	/**
     * @var Repository
     */
	 
    //protected $comments;
    /**
     * @var Repository
     */
    protected $roles;
	
    /**
     * Content plugins callback.
     *
     * @param SearchEvent $event
     */
	
    /**
     * SearchPage plugins callback.
	 *
	 * Determine areas searchable by this plugin.
	 *
	 * @return  array  An array of search areas.
	 *
	 * @since   0.1
	 */
	 
	public function onContentSearchAreas(SearchEvent $event)
	{
		if (!$dl = App::module('driven/listings')) {return;}
		static $areas = array();
		$areas = ['drivenl' => __('Listings')];
		$event->setSearchArray($areas);
	}

	public function onContentSearchAreasL()
	{
		if (!$dl = App::module('driven/listings')) {return;}
		static $areas = array();
		$areas = ['drivenl' => __('Listings')];
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
		if (!$dl = App::module('driven/listings')) {return array();}
		
		$params = App::module('friendlyit/search')->config('defaults');
		$limit 		= isset($params['limit_search_result']) ? $params['limit_search_result'] : self::PAGES_PER_PAGE;
		$markdown 	= isset($params['markdown_enabled']) ? $params['markdown_enabled'] : true ;
		
		$parameters = $event->getParameters();
		
		$text 		= $parameters[0];
		$phrase 	= $parameters[1];
		$ordering	= $parameters[2];
		$areas  	= $parameters[3]; 
		
		
		//Search on Page this content: (listings){"id":"1"}
		
		
		
		//$event->setSearchData($results);
		
		return array();
	}
	 
    /**
     * {@inheritdoc}
     */
	 
    public function subscribe()
    {
        return [
			'search.onContentSearchAreas'	=> ['onContentSearchAreas', 10],
			'search.onContentSearch'		=> ['onContentSearch', 10]
        ];

    }
	
	private function has_same_id($id, $r_array)
	{
		foreach ($r_array as $row)
		{
			if ($row['id'] == $id) return true;
		}
	return false;
	}

}