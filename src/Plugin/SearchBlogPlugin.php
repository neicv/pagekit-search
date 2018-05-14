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

use Pagekit\Blog\Model\Post;
use Pagekit\Blog\Model\Comment;

//use Pagekit\Component\Database\ORM\Repository;
use Pagekit\Event\EventSubscriberInterface;
use Pagekit\Application as App;

use PDO;
use PDOException;

/**
 * Blog search plugin.
 *
 */

class SearchBlogPlugin implements EventSubscriberInterface
{
	const PAGES_PER_PAGE = 50;
	
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
		if (!$blog = App::module('blog')) {return;}
		static $areas = array();
		$areas = ['blog' => __('Blog')];
		$event->setSearchArray($areas);
	}

	public function onContentSearchAreasL()
	{
		if (!$blog = App::module('blog')) {return;}
		static $areas = array();
		$areas = ['blog' => __('Blog')]; 
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
		if (!$blog = App::module('blog')) {return array();}

		(bool )$b_sqlite = App::db()->getDatabasePlatform()->getName() === 'sqlite';

		$params = App::module('friendlyit/search')->config('defaults');
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
				
				$word  =App::db()->quote('%' . $text . '%', false);
				$wheres2 = array();
				$wheres2[] = 'a.title LIKE '	. $word;
				$wheres2[] = 'a.excerpt LIKE '	. $word;
				$wheres2[] = 'a.content LIKE '	. $word;
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
					$wheres2[] = ($b_sqlite) ? 'php_nocase(a.excerpt) LIKE php_nocase(' . $word . ')' : 'LOWER(a.excerpt) LIKE LOWER(' . $word . ')';
					$wheres2[] = ($b_sqlite) ? 'php_nocase(a.content) LIKE php_nocase(' . $word . ')' : 'LOWER(a.content) LIKE LOWER(' . $word . ')';
					$wheres[] = implode(' OR ', $wheres2);
				}
				$where = '(' . implode(($phrase == 'all' ? ') AND (' : ') OR ('), $wheres) . ')';
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
		
		if ($b_sqlite){
			// Prepare SQLite
			$mbString       = extension_loaded('mbstring');
			$db 			= App::db()->getDatabase();

			try{
			$pdo = new PDO('sqlite:' . $db, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]); //pagekit.db
			}catch(PDOException $pe){
				echo $pe->getMessage();
			}  
			
			$prefix 					= App::db()->getPrefix();
			$db_name_blog_post	 		= $prefix.'blog_post'; 
			$db_name_blog_comment		= $prefix.'blog_comment'; 

			$mbString ? $pdo->sqliteCreateFunction('php_nocase', function($x) { return  mb_strtolower($x); }) :
			$pdo->sqliteCreateFunction('php_nocase', function($x) { return strtolower($x); });

			$orders = explode(",", $order);
			$orderBy ='';
			if ($orders) {
				$orderBy =  $orders[0] . $orders[1] ;
			}	

			//$now 		= new \DateTime::ATOM;
			$now		= new \DateTime;
			$now  		= App::db()->quote($now->format('Y-m-d H:i:s'));
			$user 		= App::user()->roles;
			$values		= implode('|', (array)$user);

			$concatestr = ' (a.excerpt || a.content) AS text';
			$strf = "OR ((',' || roles || ',') LIKE '%,$values,%')";

			// SQLite Query
			$query = $pdo->query(  'SELECT 	a.title title, a.id id, a.date date, '.$concatestr.'
									
									FROM '. $db_name_blog_post .' a 

									WHERE a.status = '. Post::STATUS_PUBLISHED.' AND a.date < '.$now.' AND '. $where .' 
                                    
                                    AND ((roles IS NULL) '.$strf.')
									GROUP BY a.title, a.content, a.excerpt, a.id
									ORDER BY ' . $orderBy .'
									LIMIT 0 ,' . $limit . '
									');

			$rows = $query->fetchall(\PDO::FETCH_ASSOC);
			}

		else{

			// MySQL Query
			$where = '(a.status = :v0 AND a.date < :v00) AND (' . $where;
			$matches['v0'] = Post::STATUS_PUBLISHED;	
			$matches['v00'] = new \DateTime;				

			$concatestr = 'CONCAT (a.excerpt, a.content) AS text';
			
			$query = App::db()->createQueryBuilder()
				->from('@blog_post a')
			
				->select('a.title title, a.id id, a.date date')
				->select($concatestr)
				->where( $where .')', $matches)
				->groupBy('a.title', 'a.content', 'a.excerpt', 'a.id');
				
			/* $limit = self::PAGES_PER_PAGE;
			$count = $query->count();
			$total = ceil($count / $limit);
			$page  = max(0, min($total - 1, $page)); */
				
			$orders = explode(",", $order);
			if ($orders) {
				$query->orderBy($orders[0], $orders[1]);
				}
			$query->offset(0)->limit($limit);
			
			$query->where(function ($query) { return $query->where('roles IS NULL')->whereInSet('roles', App::user()->roles, false, 'OR');});

			$rows = $query->get();
		}
		
		$limit -= count($rows);
		
		$index = '0';
		if (!empty($rows))
			// -----  "PDO" -----
			{
				foreach ($rows as $key => $item)
				{
					$list[$index]= new \stdclass();
					$list[$index]->title 	 		= $item['title'];
					$list[$index]->metadesc 		= '';
					$list[$index]->metakey 			= '';
					$list[$index]->created			= $item['date'];
					//$list[$index]->text 	 		= App::content()->applyPlugins($item['text'], ['item' => $item, 'markdown' => $markdown]);
					(int) $post = Post::find($item['id']); 
					$list[$index]->text 	 		= App::content()->applyPlugins($item['text'], ['item' => $item, 'markdown' => $post->get('markdown')]);
					$list[$index]->section			= __('Blog'); 
					$list[$index]->catslug 			= '';
					$list[$index]->browsernav 		= '';
					$list[$index]->href	 			= App::url('@blog/id', ['id' => $item['id']], true);
					$list[$index]->id		 		= $item['id'];
					$index++;
				}
			$rows[] = $list;
			}
		
		
		// Comments find 

		$user = App::user();
        //if ($user->hasAccess('blog: post comments')){ // ! Hu can read post comments? no have this permission in now realise 1.05

			$matches = array();
			switch ($phrase)
			{
				case 'exact':
					$word  = App::db()->quote('%' . $text . '%', false);
					$wheres2 = array();
					$wheres2[] = 'content LIKE '. $word;
					$where = '(' . implode(') OR (', $wheres2) . ')';
					break;

				case 'all':
				case 'any':
				default:
					$words = explode(' ', $text);
					$wheres = array();
					foreach ($words as $word)
					{
						$wheres2 = array();
						$word = App::db()->quote('%' . $word . '%', false);
						//$wheres2[] = 'content LIKE '. $word;
						$wheres2[] = ($b_sqlite) ? 'php_nocase(content) LIKE php_nocase(' . $word . ')' : 'LOWER(content) LIKE LOWER(' . $word . ')';
						$wheres[] = implode(' OR ', $wheres2);
					}
					$where = '(' . implode(($phrase == 'all' ? ') AND (' : ') OR ('), $wheres) . ')';
					break;
			}

			/* $limit = self::PAGES_PER_PAGE;
			$limit -= count($rows);
			$count = $query->count();
			$total = ceil($count / $limit);
			$page  = max(0, min($total - 1, $page)); */
		
			//$posts = Post::query()->where(['status = ?', 'date < ?'], [Post::STATUS_PUBLISHED, new \DateTime])->offset($page * $limit)->limit($limit);
			$posts = Post::query()->where(['status = ?', 'date < ?'], [Post::STATUS_PUBLISHED, new \DateTime])->offset(0)->limit($limit);
			$orders = explode(",", $order);
				if ($orders) {
					$posts->orderBy($orders[0], $orders[1]);
				}
				else {
					$posts->orderBy('date', 'DESC');
				}
			$posts->where(function ($query) { return $query->where('roles IS NULL')->whereInSet('roles', App::user()->roles, false, 'OR');});
			$posts = $posts->get();
		
			if ($posts) {
				if ($b_sqlite){

					// SQLite Query
					if (is_array(array_keys($posts))) {

						$values = implode(', ', array_map("self::sl_quote", array_keys($posts)));
						
						$pending = $pdo->query(  'SELECT 	id, post_id, content, created
														
												FROM '. $db_name_blog_comment .' a 

												WHERE a.status = '. Comment::STATUS_APPROVED.' AND '. $where .' AND (post_id IN ('.$values.'))
												GROUP BY post_id, content, id
												LIMIT 0 ,' . $limit . '
												');

						$pending = $pending->fetchall(\PDO::FETCH_ASSOC);
						}
						// no Post
					}
				else {

					$where = '(status = :v0) AND (' . $where;
					$matches['v0'] = Comment::STATUS_APPROVED;//"$b";	
					$pending = App::db()->createQueryBuilder()
						->from('@blog_comment')
						->select('id, post_id, content, created')
						->where( $where .')', $matches)
						->whereIn('post_id', array_keys($posts))
						//->offset($page * $limit)->limit($limit)
						->offset(0)->limit($limit)
						->groupBy('post_id','content','id');

					$pending = $pending->get();
				} 
			}
			else {
				$pending = [];
			}
			
		
			$list = null;
			$index = '0';
			if (!empty($pending))
				// ----- query "PDO" -----
				{
					foreach ($pending as $key => $item)
					{
						// Options need ?????
						//if (!$this->has_same_id($item['post_id'], $rows)){

						$list[$index]= new \stdclass();
						
						$list[$index]->title 	 		= Post::find((int) $item['post_id'] )->title;//->getTitle(); //$item['title'];
						$list[$index]->metadesc 		= '';
						$list[$index]->metakey 			= '';
						$list[$index]->created			= $item['created'];
						$list[$index]->text 	 		= App::content()->applyPlugins($item['content'], ['item' => $item, 'markdown' => $markdown]);
						$list[$index]->section			= __('Blog comments'); // PAGE NOT HAVING A SECTION
						$list[$index]->catslug 			= '';
						$list[$index]->browsernav 		= '';
						$list[$index]->href	 			= App::url('@blog/id', ['id' => $item['post_id']], true).'#comment-'.$item['id'];
						//$list[$index]->href	 			= '@blog/'.$item['post_id'].'#comment-'.$item['id'];
						$list[$index]->id		 		= $item['post_id'];
						$index++;
						//}
					}
				if (!empty($list)){
					//$rows = array();
					$rows[] = $list;
					}
				}
		
		//} // Permision Users
		
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
		//}
		return array();
	}
	 
    /**
     * {@inheritdoc}
     */
	 
    public function subscribe()
    {
        return [
			'search.onContentSearchAreas'	=> ['onContentSearchAreas', 7],
			'search.onContentSearch'		=> ['onContentSearch', 7]
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

	private function sl_quote($value_q){
		return App::db()->quote($value_q);	
	}

}