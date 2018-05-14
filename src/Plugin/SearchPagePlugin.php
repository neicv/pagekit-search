<?php
/**
 * @package     Pagekit Extension
 * @subpackage  Search.content - Page
 *
 * @copyright   Copyright (C) 2016 - 2018 Friendly-it, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Friendlyit\Search\Plugin;

use Friendlyit\Search\Helpers\EXSearchHelper;
use Friendlyit\Search\Event\SearchEvent;

//use Pagekit\Component\Database\ORM\Repository;
use Pagekit\Event\EventSubscriberInterface;

use Pagekit\Application as App;
use Pagekit\Site\Model\Page;

use PDO;
use PDOException;

/**
 * Page search plugin.
 *
 */

class SearchPagePlugin implements EventSubscriberInterface
{
	const PAGES_PER_PAGE = 50;
	
	const STATUS_PUBLISHED = 1;
	
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

		(bool )$b_sqlite = App::db()->getDatabasePlatform()->getName() === 'sqlite';

		$params 	= App::module('friendlyit/search')->config('defaults');
		$limit 		= isset($params['limit_search_result']) ? $params['limit_search_result'] : self::PAGES_PER_PAGE;
		$markdown 	= isset($params['markdown_enabled']) ? $params['markdown_enabled'] : true ;
		
		$parameters = $event->getParameters();
		
		$text 		= $parameters[0];
		$phrase 	= $parameters[1];
		$ordering	= $parameters[2];
		$areas  	= $parameters[3]; 
		
		$searchText = $text;

		if (is_array($areas) && !array_intersect($areas, array_keys($this->onContentSearchAreasL())))
		{
			return array();
		}

		$text = trim($text);
		if ($text === '')
		{
			return array();
		}
		
		$text = EXSearchHelper::strip_data(trim($text));
		$text = stripslashes($text); 
		$text = htmlspecialchars($text); 

		$matches = array();
		switch ($phrase)
		{
			case 'exact':
				
				$text =App::db()->quote('%' . $text . '%', false);
				$wheres2 = array();
				$wheres2[] = 'a.title LIKE '.$text;
				$wheres2[] = 'a.content LIKE '. $text;
				$where = '(' . implode(') OR (', $wheres2) . ')';
				break;

			case 'all':
			case 'any':
			default:
				$words = explode(' ', $text);
				$wheres = array();
				foreach ($words as $word)
				{
					$word = App::db()->quote('%' . $word . '%', false);
					$wheres2 = array();
					$wheres2[] = ($b_sqlite) ? 'php_nocase(a.title) LIKE php_nocase(' . $word . ')' : 'LOWER(a.title) LIKE LOWER(' . $word . ')';
					$wheres2[] = ($b_sqlite) ? 'php_nocase(a.content) LIKE php_nocase(' . $word . ')' : 'LOWER(a.content) LIKE LOWER(' . $word . ')';
					$wheres[] = implode(' OR ', $wheres2);
				}

				$where = '(' . implode(($phrase == 'all' ? ') AND (' : ') OR ('), $wheres) . ')';
				break;
		}
	
		
		switch ($ordering)
		{
			case 'oldest':
				$order = 'a.id, ASC';
				break;

			case 'popular':
				//$order = "'a.hits DESC'";
				//break;

			case 'alpha':
				$order = 'a.title, ASC';
				break;

			case 'category':
				$order = 'a.title, ASC';
				break;

			case 'newest':
			default:
				$order = 'a.id, DESC';
				break;
		}

		$rows = array();

		if ($b_sqlite){
			$mbString       = extension_loaded('mbstring');
			$db 			= App::db()->getDatabase();

			try{
			$pdo = new PDO('sqlite:' . $db, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]); //pagekit.db
			}catch(PDOException $pe){
				echo $pe->getMessage();
			}  
			
			$prefix 					= App::db()->getPrefix();
			$db_name_system_page 		= $prefix.'system_page'; 
			$db_name_system_node 		= $prefix.'system_node'; 

			$mbString ? $pdo->sqliteCreateFunction('php_nocase', function($x) { return  mb_strtolower($x); }) :
			$pdo->sqliteCreateFunction('php_nocase', function($x) { return strtolower($x); });

			$orders = explode(",", $order);
			$orderBy ='';
			if ($orders) {
				$orderBy =  $orders[0] . $orders[1] ;
			}	

			$concatestr = '(\'%"defaults":{"id":\'|| cast(a.id as char) || \'}%\')';

			$user = App::user()->roles;
			$values = implode('|', (array)$user);

			// use self defined func regexp
			//$strf = 'regexp '.$pdo->quote("(^|,)({$values})($|,)").'';
			//AND ((roles IS NULL) OR (roles '.$strf .'))

			$strf = "OR ((',' || roles || ',') LIKE '%,$values,%')";

			$query = $pdo->query(  'SELECT 	a.id AS page_id, a.title, a.content, a.data, c.id, c.type, c.data, c.link, c.status
									FROM '. $db_name_system_page .' a 
									INNER JOIN '. $db_name_system_node .' c
									ON (c.type LIKE ("page") AND c.data LIKE '. $concatestr .')
									WHERE c.status = '. self::STATUS_PUBLISHED.' AND  ('. $where .') 
                                    
                                    AND ((roles IS NULL) '.$strf.')
									GROUP BY a.title, a.content, a.id, c.id
									ORDER BY ' . $orderBy .'
									LIMIT 0 ,' . $limit . '
									');

			$rows = $query->fetchall(\PDO::FETCH_ASSOC);
			}
		else{

			$matches['v00'] = 'page';
			$matches['v0'] = self::STATUS_PUBLISHED;			
			$where = '(c.status = :v0) AND (' . $where;
			$concatestr =  'CONCAT (\'%"defaults":{"id":\', cast(a.id as char),\'}%\')';
			
			$query = App::db()->createQueryBuilder()
				->select('a.id AS page_id, a.title, a.content, a.data, c.id, c.type, c.data, c.link, c.status')
				->from('@system_page a')
				->join('@system_node c', '(c.type LIKE :v00 AND c.data LIKE '.$concatestr.')', 'INNER')
				->Where( $where .')', $matches);

		    /**
			* Creates and adds an "order by" to the query.
			*
			* @param  string $sort
			* @param  string $order
			* @return self
			*/
			$orders = explode(",", $order);
			if ($orders) {
				$query->orderBy($orders[0], $orders[1]);
			}
		
			$query->groupBy('a.title, a.content','a.id','c.id');
			//$query->offset($page * $limit)->limit($limit);
			$query->offset(0)->limit($limit);
			$query->where(function ($query) { return $query->where('roles IS NULL')->whereInSet('roles', App::user()->roles, false, 'OR');});
			$rows = $query->get();
		}
		//$user = App::user();
		
		$list = null;
		$index = '0';
		if (!empty($rows))
			{
				foreach ($rows as $key => $item)
				{
					$list[$index]= new \stdclass();
					// include support on/off title, but in Pagekit 1.0.13 NOT USE
					//$page = Page::find($item['page_id']);
					//($page->get('title')) ? $list[$index]->title = $item['title'] : $list[$index]->title = $index + 1;
					$list[$index]->title 	 		= $item['title'];
					$list[$index]->metadesc 		= '';
					$list[$index]->metakey 			= '';
					$list[$index]->created			= '';
					$page = Page::find($item['page_id']);
					//$list[$index]->text 	 		= App::content()->applyPlugins($item['content'], ['item' => $item, 'markdown' => $markdown]);
					$list[$index]->text 	 		= App::content()->applyPlugins($item['content'], ['item' => $item, 'markdown' => $page->get('markdown')]);
					$list[$index]->section			= __('Pages');//__('Uncategorised'); // PAGE NOT HAVING A SECTION
					$list[$index]->catslug 			= '';
					$list[$index]->browsernav 		= '';
					$list[$index]->href	 			= App::url($item['link']);
					$index++;
				}
			$rows = array();
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