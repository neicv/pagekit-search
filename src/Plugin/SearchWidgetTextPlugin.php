<?php
/**
 * @package     Pagekit Extension
 * @subpackage  Search.content - Widget/Text
 *
 * @copyright   Copyright (C) 2016 - 2018 Friendly-it, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Friendlyit\Search\Plugin;

use Friendlyit\Search\Helpers\EXSearchHelper;
use Friendlyit\Search\Event\SearchEvent;

use Pagekit\Event\EventSubscriberInterface;

use Pagekit\Application as App;
use Pagekit\Site\Model\Node;
use Pagekit\User\Model\Role;
use Pagekit\Widget\Model\Widget;

use PDO;
use PDOException;

/**
 * Page search plugin.
 *
 */

class SearchWidgetTextPlugin implements EventSubscriberInterface
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
		$areas = ['widget-txt' => __('Widget')];
		$event->setSearchArray($areas);
	}

	public function onContentSearchAreasL()
	{
		static $areas = array();
		$areas = ['widget-txt' => __('Widget')]; 
		return $areas;
	}
	
	/**
	 * Search content (Widget - Text).
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
				$text = str_replace('"', "", json_encode($text));
				$text =App::db()->quote($text);
				$text = str_replace("'", "", $text);
				$text =App::db()->quote('%' . $text . '%', false);
				$wheres2 = array();
				$wheres2[] = 'a.title LIKE '.$text;
				$wheres2[] = 'a.data LIKE '. $text;
				$where = '(' . implode(') OR (', $wheres2) . ')';
				break;

			case 'all':
			case 'any':
			default:
				$words = explode(' ', $text);
				$wheres = array();
				foreach ($words as $word)
				{
					$word2 = json_encode(str_split($word), JSON_UNESCAPED_UNICODE);
					$word = App::db()->quote('%' . $word . '%', false);
					$word2 = App::db()->quote('%' . $word2 . '%', false);
					$wheres2 = array();
					$wheres2[] = ($b_sqlite) ? 'php_nocase(a.title) LIKE php_nocase(' . $word . ')' : 'LOWER(a.title) LIKE LOWER(' . $word . ')';
					$wheres2[] = ($b_sqlite) ? 'php_nocase(a.data) LIKE php_nocase(' . $word . ')' : 'LOWER(a.data) LIKE LOWER(' . $word . ')';
					$wheres2[] = 'a.title LIKE ' . $word2 . '';
					$wheres2[] = 'a.data LIKE ' . $word2 . '';
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
			$db_name_system_widget 		= $prefix.'system_widget'; 
			$db_name_system_node 		= $prefix.'system_node'; 

			$mbString ? $pdo->sqliteCreateFunction('php_nocase', function($x) { return  mb_strtolower($x); }) :
			$pdo->sqliteCreateFunction('php_nocase', function($x) { return strtolower($x); });

			$orders = explode(",", $order);
			$orderBy ='';
			if ($orders) {
				$orderBy =  $orders[0] . $orders[1] ;
			}	

			$user = App::user()->roles;
			$values = implode('|', (array)$user);
			$strf_a = "OR ((',' || a.roles || ',') LIKE '%,$values,%')";
			$strf_c = "OR ((',' || c.roles || ',') LIKE '%,$values,%')";
			$strf_0 = "(a.nodes IS NULL) OR (',' || a.nodes || ',') LIKE '%,' || c.id || ',%'";

			$query = $pdo->query(  'SELECT 	a.id AS widget_id, a.title AS widget_title, a.data, c.id AS node_id, c.title AS node_title, c.link
									FROM '. $db_name_system_widget .' a 
									INNER JOIN '. $db_name_system_node .' c

									ON ((c.type LIKE "page" OR c.type LIKE "blog")  AND a.type LIKE ("system/text") AND  '.$strf_0.' )
									WHERE c.status = '. self::STATUS_PUBLISHED.'  AND a.status = '. self::STATUS_PUBLISHED.'  AND  ('. $where .')
									AND ((a.roles IS NULL) '.$strf_a.') 
									AND ((c.roles IS NULL) '.$strf_c.')
									GROUP BY a.title, a.data, a.id, c.id
									ORDER BY ' . $orderBy .'
									LIMIT 0 ,' . $limit . '
									');

			$rows = $query->fetchall(\PDO::FETCH_ASSOC);
			}

		else{

			$strf_0 = "(a.nodes IS NULL) OR (',' || a.nodes || ',') LIKE '%,' || c.id || ',%'";
			
			$query = App::db()->createQueryBuilder()
				->select( 'a.id AS widget_id, a.title AS widget_title, a.data, c.id AS node_id, c.title AS node_title, c.link')
				->from('@system_widget a')
				->join('@system_node c', '((c.type LIKE "page" OR c.type LIKE "blog")  AND a.type LIKE ("system/text") AND  '.$strf_0.')', 'INNER')
				->where( 'c.status = '. self::STATUS_PUBLISHED.'  AND a.status = '. self::STATUS_PUBLISHED.'  AND  ('. $where .')');
			
			//$query->where(function ($query) { return $query->where('a.nodes IS NULL')->whereInSet('a.nodes', 'c.id', false, 'OR');});
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
		
			$query->groupBy('a.title', 'a.data','a.id','c.id');
			$query->offset(0)->limit($limit);
			$query->where(function ($query) { return $query->where('a.roles IS NULL')->whereInSet('a.roles', App::user()->roles, false, 'OR');});
			$query->where(function ($query) { return $query->where('c.roles IS NULL')->whereInSet('c.roles', App::user()->roles, false, 'OR');});
			$rows = $query->get();
		}
		
		$list = null;
		$index = '0';
		if (!empty($rows))
			{
				$last_id = 0;
				foreach ($rows as $key => $item)
				{

					// NEED COMPACT RESULT
					// LOOP ON Widget ID
					// Compact ID Nodes
					//  $page = $this->getPage($node->get('defaults.id', 0));

					if ($last_id === $item['widget_id']) continue;

					$list[$index]= new \stdclass();

					$widget = Widget::find($item['widget_id']);
					//$node = Node::find($item['node_id']);
					//$page = Page::find($node->get('defaults.id', 0));

					// include support on/off title, but in Pagekit 1.0.13 NOT USE
					//$page = Page::find($item['page_id']);
					//($page->get('title')) ? $list[$index]->title = $item['title'] : $list[$index]->title = $index + 1;
					$list[$index]->title 	 		= $item['widget_title'];
					$list[$index]->metadesc 		= '';
					$list[$index]->metakey 			= '';
					$list[$index]->created			= '';

					$list[$index]->text 	 		= App::content()->applyPlugins($widget->get('content'), ['widget' => $widget, 'markdown' => $widget->get('markdown')]);
					$list[$index]->section			= __('Widget');
					$list[$index]->catslug 			= '';
					$list[$index]->browsernav 		= '';
					$list[$index]->href	 			= App::url($item['link']);
					$last_id = $item['widget_id'];
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
			'search.onContentSearchAreas'	=> ['onContentSearchAreas', 6],
			'search.onContentSearch'		=> ['onContentSearch', 6]
        ];
    }
}