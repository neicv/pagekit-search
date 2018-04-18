<?php
/**
 * @package     Pagekit Extension
 * @subpackage  Search.content - Driven / Listings
 *
 * @copyright   Copyright (C) 2018 Friendly-it, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// in Driven / Listngs ver 1.0.6  not use 'featured_from' and 'featured_to' attributes, so in this plugin not use too...

namespace Friendlyit\Search\Plugin;

use Friendlyit\Search\Helpers\EXSearchHelper;
use Friendlyit\Search\Event\SearchEvent;
use Pagekit\Site\Model\Page;
use Driven\Listings\Model\item;
use Driven\Listings\Model\Listing;
use Driven\Listings\Model\ListingCategory;


//use Pagekit\Component\Database\ORM\Repository;
use Doctrine\DBAL\Platform\SqlitePlatform;
//use Doctrine\DBAL\Driver\PDOConnection;
use Doctrine\DBAL\Connection;
use Pagekit\Event\EventSubscriberInterface;
use Pagekit\Application as App;


use PDO;

/**
 * DrivenListings search plugin.
 *
 */

class SearchDrivenListingsPlugin implements EventSubscriberInterface
{
	const PAGES_PER_PAGE = 50;
	const STATUS_ACTIVE = 1;
	const STATUS_PUBLISHED = 1;

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

		$params = App::module('friendlyit/search')->config('pluginDrivenListings');
		(bool) $use_item_href 		= isset($params['use_item_href']) ? $params['use_item_href'] : false ;
		(bool) $use_items_area 		= isset($params['use_items_area']) ? $params['use_items_area'] : true ;
		(bool) $use_category_area 	= isset($params['use_category_area']) ? $params['use_category_area'] : true ;
		(bool) $use_listing_area 	= isset($params['use_listing_area']) ? $params['use_listing_area'] : true ;
		(bool) $use_sharp_links 	= isset($params['use_sharp_links']) ? $params['use_sharp_links'] : false ;

		
		$parameters = $event->getParameters();
		
		$text 		= $parameters[0];
		$phrase 	= $parameters[1];
		$ordering	= $parameters[2];
		$areas  	= $parameters[3]; 
		
		$searchText = $text;

		/*if (is_array($areas))
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
		} */

		if (is_array($areas) && !array_intersect($areas, array_keys($this->onContentSearchAreasL())))
		{
			return array();
		}

		
		if (App::db()->getDatabasePlatform()->getName() === 'sqlite') $b_sqlite = true; else $b_sqlite = false;

		$date  						= new \DateTime();
		$now   						= $date;//->toSql();
		$prefix 					= App::db()->getPrefix();
		$db_name_listings_item 		= $prefix.'listings_item'; 
		$db_name_listings_category	= $prefix.'listings_category'; 
		$db_name_listings_listing 	= $prefix.'listings_listing'; 

		
		$text = trim($text);
		if ($text == '')
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
				
				$word  =App::db()->quote('%' . $text . '%', false);
				$wheres2 = array();
				$wheres2[] = 'a.title LIKE '	. $word;
				$wheres2[] = 'a.description LIKE '	. $word;
				
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
					$wheres2[] = 'php_nocase(a.title) LIKE php_nocase(' . $word . ')';
					//$wheres2[] = 'LOWER(a.title) LIKE LOWER(' . $word . ')';
					$wheres2[] = 'php_nocase(a.description) LIKE php_nocase(' . $word . ')';//!!
					//$wheres2[] = 'LOWER(a.description) LIKE LOWER(' . $word . ')';//!!
					

					$wheres[] = implode(' OR ', $wheres2);
				}
				$where = '(' . implode(($phrase === 'all' ? ') AND (' : ') OR ('), $wheres) . ')';
				break;
		}
		
		switch ($ordering)
		{
			case 'oldest':
				$order = 'date, ASC';
				break;

			case 'popular':
				//$order = "'a.hits DESC'";
				//break;

			case 'alpha':
				$order = 'title, ASC';
				break;

			case 'category':
				$order = 'title, ASC'; 
				break;

			case 'newest':
			default:
				$order = 'date, DESC'; 
				break;
		}

		$rows = array();

		// -----  "PDO" -----

		$mbString       = extension_loaded('mbstring');
		$db 	= App::db()->getDatabase();

		try{
		$pdo = new PDO('sqlite:' . $db, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]); //pagekit.db
		}catch(PDOException $pe){
        	echo $pe->getMessage();
    	}  
		
		//$pdo->sqliteCreateFunction('php_nocase', function($x) { return $x = $mbString ? mb_strtolower($x) : strtolower($x); });

		if ($mbString ) {$pdo->sqliteCreateFunction('php_nocase', function($x) { return  mb_strtolower($x); });}
		else {$pdo->sqliteCreateFunction('php_nocase', function($x) { return strtolower($x); });}

		$orders = explode(",", $order);
		$orderBy ='';
		if ($orders) {
			$orderBy =  $orders[0] . $orders[1] ;
		}


		/*
		// Search keywords on Listings 
		*/

		if  ($use_listing_area){
			$query = $pdo->query('SELECT 	a.id AS id,
											a.title AS title,
											a.description AS description,
											a.modified_on AS date,
											a.status AS listing_status
											
									FROM '. $db_name_listings_listing .' a 

									WHERE listing_status = '. self::STATUS_ACTIVE.' AND  ('. $where .')
									GROUP BY a.title, a.description
									ORDER BY ' . $orderBy .'
									LIMIT 0 ,' . $limit . '
									');

			$listing = $query->fetchall(\PDO::FETCH_ASSOC);

			$index = '0';
			$list =array ();
			if (!empty($listing))
			{
				foreach ($listing as $key => $item)
				{
					// this check user right on "page" 
					$f_href = $this->find_url_page($item['id'], $b_sqlite);
					if (!$f_href) continue; 

					$list[$index]= new \stdclass();
					$list[$index]->title 	 		= $item['title'];
					$list[$index]->metadesc 		= '';
					$list[$index]->metakey 			= '';
					$list[$index]->created 			= date("Y-m-d H:i:s", $item['date']);
					$list[$index]->text 	 		= App::content()->applyPlugins($item['description'], ['item' => $item, 'markdown' => $markdown]);
					$list[$index]->section			= __('Listings:listing'); // PAGE NOT HAVING A SECTION
					$list[$index]->catslug 			= '';
					$list[$index]->browsernav 		= '';
					$list[$index]->href	 			= $f_href[0]->href ;
					$list[$index]->id		 		= $item['id'];
					$index++;
				}
			
			if (count($list)) $rows[] = $list;
			}
			$limit -= count($list);
		}


		/*
		// Search keywords on Listings Items
		*/
		if  ($use_items_area){
			$query = $pdo->query('SELECT 	a.id AS item_id,
											a.listing_id AS id,
											a.title AS title,
											a.description AS description,
											a.modified_on AS date,
											a.status AS item_status,
											a.link AS link,
											c.status AS category_status,
											l.status AS listing_status
											
									FROM '. $db_name_listings_item .' a 

									INNER JOIN '. $db_name_listings_category .' c
									ON a.category_id = c.id

									INNER JOIN '. $db_name_listings_listing .' l
									ON a.listing_id = l.id

									WHERE item_status = '. self::STATUS_ACTIVE.' AND category_status = '. self::STATUS_ACTIVE.' AND listing_status = '. self::STATUS_ACTIVE.' AND  ('. $where .')
									GROUP BY a.title, a.description
									ORDER BY ' . $orderBy .'
									LIMIT 0 ,' . $limit . '
									');

			$l_items = $query->fetchall(\PDO::FETCH_ASSOC);

			//->join('@system_node c', '(c.type LIKE :v0 AND c.data LIKE '.$concatestr.')', 'INNER')

			
			/*
			$query 	= App::db()->createQueryBuilder()
			//$query 	= $pdo->createQueryBuilder()
				->from('@listings_item a')
			
				->select('a.listing_id AS id, a.title AS title, a.description AS description, a.modified_on AS date, status') //featured_from featured_to
				//->select($concatestr)
				//->where( $where .')', $matches)
				->where( $where )
				//->where('status = ?', [1]) //self::STATUS_ACTIVE
				->groupBy('a.title', 'a.description');
				
			//$limit = self::PAGES_PER_PAGE;
			//$count = $query->count();
			//$total = ceil($count / $limit);
			//$page  = max(0, min($total - 1, $page));
		

				
			$orders = explode(",", $order);
			if ($orders) {
				$query->orderBy($orders[0], $orders[1]);
				}
			$query->offset(0)->limit($limit);
			
			//$query->where(function ($query) { return $query->where('roles IS NULL')->whereInSet('roles', App::user()->roles, false, 'OR');});
			
			$rows = $query->get();
			*/



			$index = '0';
			$list =array ();
			if (!empty($l_items))
				// -----  "PDO" -----
			{
				foreach ($l_items as $key => $item)
				{
					if ($item['link']) {
						// this check user right on "page" 
						if (!$this->find_url_page($item['id'], $b_sqlite)) continue; }
					if ($item['link'] === "" || !$use_item_href){
						$f_href = $this->find_url_page($item['id'], $b_sqlite);
						if ($f_href){
							// Put this code in your template
							// <div id="dv-listings-item-{{item.id}}"></div>
							// for GoTo selected item
							//. '#dv-listings-item-' . $item['item_id']
							$item['link'] 				= $f_href[0]->href;
							if ($use_sharp_links){
								$item['link'] 			.= '#dv-listings-item-' . $item['item_id']; }
								//$item['link'] 			=."#dv-listings-item-" .$item['item_id'];}
							}
						else continue;
					}
					$list[$index]= new \stdclass();
					$list[$index]->title 	 		= $item['title'];
					$list[$index]->metadesc 		= '';
					$list[$index]->metakey 			= '';
					$list[$index]->created 			= date("Y-m-d H:i:s", $item['date']);
					//$list[$index]->created			= "@". $item['date'];

					//$list[$index]->text 	 		= $item['text'];
					$list[$index]->text 	 		= App::content()->applyPlugins($item['description'], ['item' => $item, 'markdown' => $markdown]);
					$list[$index]->section			= __('Listings:item'); // PAGE NOT HAVING A SECTION
					$list[$index]->catslug 			= '';
					$list[$index]->browsernav 		= '';
					$list[$index]->href	 			= $item['link'];
					$list[$index]->id		 		= $item['item_id'];
					$index++;
				}
			if (count($list)) $rows[] = $list;
			}
			
			$limit -= count($list);
		}
		
		/*
		// Search keywords on Listings Category
		*/

		if  ($use_category_area){
			$query = $pdo->query('SELECT 	a.listing_id AS id,
											a.title AS title,
											a.description AS description,
											a.modified_on AS date,
											a.status AS category_status,
											l.status AS listing_status
											
									FROM '. $db_name_listings_category .' a 


									INNER JOIN '. $db_name_listings_listing .' l
									ON a.listing_id = l.id

									WHERE category_status = '. self::STATUS_ACTIVE.' AND listing_status = '. self::STATUS_ACTIVE.' AND  ('. $where .')
									GROUP BY a.title, a.description
									ORDER BY ' . $orderBy .'
									LIMIT 0 ,' . $limit . '
									');

			$category = $query->fetchall(\PDO::FETCH_ASSOC);

			$index = '0';
			$list =array ();
			if (!empty($category))
			{
				foreach ($category as $key => $item)
				{
					// this check user right on "page" 
					$f_href = $this->find_url_page($item['id'], $b_sqlite);
					if (!$f_href) continue; 

					$list[$index]= new \stdclass();
					$list[$index]->title 	 		= $item['title'];
					$list[$index]->metadesc 		= '';
					$list[$index]->metakey 			= '';
					$list[$index]->created 			= date("Y-m-d H:i:s", $item['date']);
					$list[$index]->text 	 		= App::content()->applyPlugins($item['description'], ['item' => $item, 'markdown' => $markdown]);
					$list[$index]->section			= __('Listings:category'); // PAGE NOT HAVING A SECTION
					$list[$index]->catslug 			= '';
					$list[$index]->browsernav 		= '';
					$list[$index]->href	 			= $f_href[0]->href;
					$list[$index]->id		 		= $item['id'];
					$index++;
				}
			
			if (count($list)) $rows[] = $list;
			}
			$limit -= count($list);
		}
		//
		



		$results = array();
		if (count($rows))
		{
			foreach ($rows as $row)
			{
				$new_row = array();

				foreach ($row as $article)
				{

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


	private function find_url_page($h_id, $b_sqlite) {

		//Search on Page this content: (listings){"id":"1"}

		if (!$b_sqlite) {$concatestr = 'CONCAT (\'%"defaults":{"id":\', cast(a.id as char),\'}%\')';}
		else {$concatestr = '(\'%"defaults":{"id":\'|| cast(a.id as char) || \'}%\')';}
		
		$matches['v0'] = 'page';
		$matches['v1'] = self::STATUS_PUBLISHED;	
		$where = '(c.status = :v1) AND ( a.content LIKE (\'%(listings){"id":"'.$h_id.'"%\')';

		$query = App::db()->createQueryBuilder()
			->from('@system_page a')
			->join('@system_node c', '(c.type LIKE :v0 AND c.data LIKE '.$concatestr.')', 'INNER')
			->Where( $where .')', $matches)
			->where(function ($query) { return $query->where('c.roles IS NULL')->whereInSet('c.roles', App::user()->roles, false, 'OR');})
			->groupBy('a.id');
		$rows = $query->get();

		$list = null;
		$index = '0';
		if (!empty($rows))
		{
			foreach ($rows as $key => $item)
			{
				$list[$index]= new \stdclass();
				$list[$index]->href	 			= $item['link'];
				$index++;
			}
		}
		return ($list);
	}
}
