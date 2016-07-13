<?php
/**
 * @package     Pagekit Extension
 * @subpackage  Search.content - Page
 *
 * @copyright   Copyright (C) 2016 Friendly-it, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Friendlyit\Search\Plugin;

use Friendlyit\Search\Helpers\EXSearchHelper;
use Friendlyit\Search\Event\SearchEvent;


use Pagekit\Component\Database\ORM\Repository;
use Pagekit\Event\EventSubscriberInterface;

use Pagekit\Application as App;
use Pagekit\Site\Model\Page;

/**
 * Page search plugin.
 *
 */

class SearchPagePlugin implements EventSubscriberInterface
{
	const PAGES_PER_PAGE = 50;
	
	const STATUS_PUBLISHED = 1;
	/**
     * @var Repository
     */
    protected $pages;

    /**
     * @var Repository
     */
    protected $roles;
	
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
		static $areas = array();
		$areas = ['pages' => __('Pages')];
		$event->setSearchArray($areas);
	}

	public function onContentSearchAreasL()
	{
		static $areas = array();
		$areas = ['pages' => __('Pages')]; 
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
		$params = App::module('friendlyit/search')->config('defaults');
		$limit 	= (!$params['limit_search_result']) ? self::PAGES_PER_PAGE : $params['limit_search_result'];
		
		$parameters = $event->getParameters();
		
		$text 		= $parameters[0];
		$phrase 	= $parameters[1];
		$ordering	= $parameters[2];
		$areas  	= $parameters[3]; 
		
		$searchText = $text;
		if (is_array($areas))
		{
			if (count($areas))
			{
				if (!array_intersect($areas, array_keys($this->onContentSearchAreasL())))
				{
					$a = array();
					$event->setSearchData($a);
					return $a;
				}
			}
		} 

		$text = trim($text);
		$text = stripslashes($text); 
		$text = htmlspecialchars($text); 
		
		if ($text == '')
		{
			return array();
		}
		

		$matches = array();
		switch ($phrase)
		{
			case 'exact':
				//$text = $this['db']->quote('%' . $this['db']->escape($text, true) . '%', false);
				$words = $text;
				$wheres2 = array();
				$wheres2[] = 'a.title LIKE :v1';// . $words;
				$wheres2[] = 'a.content LIKE :v1';// . $words;
				$where = '(' . implode(') OR (', $wheres2) . ')';
				$matches['v1'] = "%{$text}%"; 
				break;

			case 'all':
			case 'any':
			default:
				$words = explode(' ', $text);
				$wheres = array();
				$key_index = 1;
				foreach ($words as $word)
				{
					//$word = $this['db']->quote('%' . $this['db']->getEscaped($word, true) . '%', false);
					$wheres2 = array();
					$wheres2[] = 'a.title LIKE :v'. $key_index;//1';// . $word;
					$wheres2[] = 'a.content LIKE :v'. $key_index;//1';//. $word;
					$wheres[] = implode(' OR ', $wheres2);
					$matches['v' .$key_index] = "%{$word}%";
					++$key_index;
				}

				$where = '(' . implode(($phrase == 'all' ? ') AND (' : ') OR ('), $wheres) . ')';
				break;
		}
	
		
		switch ($ordering)
		{
			case 'oldest':
				$order = 'a.id, ASC';//"'a.created ASC'";
				break;

			case 'popular':
				//$order = "'a.hits DESC'";
				//break;

			case 'alpha':
				$order = 'a.title, ASC';//array('title'=>'ASC');
				break;

			case 'category':
				$order = 'a.title, ASC'; //array('title'=>'ASC'); 
				break;

			case 'newest':
			default:
				$order = 'a.id, DESC'; //array('title'=>'DESC');// DESC
				break;
		}

		$rows = array();
			
			$type = 'page';
			$b = self::STATUS_PUBLISHED;
			$aid = 'a.id'; 
			
			$id_string = '"defaults":{"id":';
			$matches['v00'] = "%{$type}%";
			//$matches['v01'] = "%{$id_string}%".$aid; 			
			$matches['v0'] = "$b";			
			
			$where = '(c.status = :v0) AND (' . $where;

	
			
			$query = App::db()->createQueryBuilder()
				->from('@system_page a')
				//->join('@system_node c', '(c.type LIKE :v00 AND c.data LIKE :v01)', 'INNER')
				->join('@system_node c', '(c.type LIKE :v00 AND c.data LIKE CONCAT (\'%"defaults":{"id":\', cast(a.id as char),\'%\'))', 'INNER')
				->Where( $where .')', $matches);

			$orders = explode(",", $order);
			if ($orders) {
				$query->orderBy($orders[0], $orders[1]);
				}
		    /**
			* Creates and adds an "order by" to the query.
			*
			* @param  string $sort
			* @param  string $order
			* @return self
			*/
			$query->groupBy('a.title, a.content');
			//$query->offset($page * $limit)->limit($limit);
			$query->offset(0)->limit($limit);
			$query->where(function ($query) { return $query->where('roles IS NULL')->whereInSet('roles', App::user()->roles, false, 'OR');});
			$rows = $query->get();

		$user = App::user();

		
		$list = null;
		$index = '0';
		if (!empty($rows))
			{
				foreach ($rows as $key => $item)
				{
					$list[$index]= new \stdclass();
					$list[$index]->title 	 		= $item[title];//= $item->getTitle();
					$list[$index]->metadesc 		= '';
					$list[$index]->metakey 			= '';
					$list[$index]->created			= '';
					$list[$index]->text 	 		= $item[content];//= $item->getContent();
					$list[$index]->section			= __('Uncategorised'); // PAGE NOT HAVING A SECTION
					$list[$index]->catslug 			= '';
					$list[$index]->browsernav 		= '';
					$list[$index]->href	 			= App::url($item[link]);//$item[path];//= $item->getUrl();
					$index++;
				}
			$rows[] = $list;
			}
		
		$results = array();
		
		if (count($rows))
		{
			foreach ($rows as $row)
			{
				$new_row = array();

				foreach ($row as $article)
				{
					//if (EXSearchHelper::checkNoHTML($article, $searchText, array('text', 'title', 'metadesc', 'metakey')))
					if (EXSearchHelper::checkNoHTML($article, $searchText, array('text', 'title')))
					{
						$new_row[] = $article;
					}
				}
			
				$results = array_merge($results, (array) $new_row);
			}
		}
		
		$event->setSearchData($results);
		return array();
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