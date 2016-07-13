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


use Pagekit\Component\Database\ORM\Repository;

use Pagekit\Event\EventSubscriberInterface;

use Pagekit\Application as App;


/**
 * Blog search plugin.
 *
 */

class SearchBlogPlugin implements EventSubscriberInterface
{
	const PAGES_PER_PAGE = 50;
	/**
     * @var Repository
     */
    protected $posts;

	/**
     * @var Repository
     */
	 
    protected $comments;
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
		if ($text == '')
		{
			return array();
		}
	
		$text = trim($text); 
		$text = stripslashes($text); 
		$text = htmlspecialchars($text); 

		$matches = array();
		switch ($phrase)
		{
			case 'exact':
				//$text = $this['db']->quote('%' . $this['db']->escape($text, true) . '%', false);
				$words = $text;
				$wheres2 = array();
				$wheres2[] = 'a.title LIKE :v1';// . $words;
				$wheres2[] = 'a.excerpt LIKE :v1';// . $words; 
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
					//Upper($word);
					//$word = $this['db']->quote('%' . $this['db']->getEscaped($word, true) . '%', false);
					$wheres2 = array();
					$wheres2[] = 'a.title LIKE :v'. $key_index;//1';// . $word;
					//$wheres2[] = 'Upper(a.excerpt) LIKE Upper(:v'. $key_index . ')';// . $words; 
					$wheres2[] = 'a.excerpt LIKE :v'. $key_index;// . $words; 
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
				$order = 'date, ASC';//"'a.created ASC'";
				break;

			case 'popular':
				//$order = "'a.hits DESC'";
				//break;

			case 'alpha':
				$order = 'title, ASC';//array('title'=>'ASC');
				break;

			case 'category':
				$order = 'title, ASC'; //array('title'=>'ASC'); //$order = 'c.title ASC, a.title ASC';
				break;

			case 'newest':
			default:
				$order = 'date, DESC'; //array('title'=>'DESC');// DESC
				break;
		}

		$rows = array();
		

		$where = '(a.status = :v0 AND a.date < :v00) AND (' . $where;
		$matches['v0'] = Post::STATUS_PUBLISHED;//"$b";	
		$matches['v00'] = new \DateTime;				

		// -----  "PDO" -----
		
		$query = App::db()->createQueryBuilder()
			->from('@blog_post a')
		
			->select('a.title title, a.id id, a.date date')
			->select($this->concatenate(array('a.excerpt', 'a.content')) . ' text')
			->where( $where .')', $matches)
			->groupBy('a.title', 'a.content', 'a.excerpt');
			
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
					$list[$index]->text 	 		= $item['text'];
					$list[$index]->section			= __('Blog'); // PAGE NOT HAVING A SECTION
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
					$words = $text;
					$wheres2 = array();
					$wheres2[] = 'content LIKE :v1';
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
						$wheres2 = array();
						$wheres2[] = 'content LIKE :v'. $key_index;//1';//. $word;
						$wheres[] = implode(' OR ', $wheres2);
						$matches['v' .$key_index] = "%{$word}%";
						++$key_index;
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
		
			$where = '(status = :v0) AND (' . $where;
			$matches['v0'] = Comment::STATUS_APPROVED;//"$b";	

			if ($posts) {
				$pending = App::db()->createQueryBuilder()
					->from('@blog_comment')
					->select('id, post_id, content, created')
					->where( $where .')', $matches)
					->whereIn('post_id', array_keys($posts))
					->offset($page * $limit)->limit($limit)
					->groupBy('post_id','content');

					$pending = $pending->get();
			} else {
				$pending = [];
			}

		
			$list = null;
			$index = '0';
			if (!empty($pending))
				// ----- query "PDO" -----
				{
					foreach ($pending as $key => $item)
					{
						if (!$this->has_same_id($item['post_id'], $rows)){

						$list[$index]= new \stdclass();
						
						$list[$index]->title 	 		= Post::find((int) $item['post_id'] )->title;//->getTitle(); //$item['title'];
						$list[$index]->metadesc 		= '';
						$list[$index]->metakey 			= '';
						$list[$index]->created			= $item['created'];
						$list[$index]->text 	 		= $item['content'];
						$list[$index]->section			= __('Blog comments'); // PAGE NOT HAVING A SECTION
						$list[$index]->catslug 			= '';
						$list[$index]->browsernav 		= '';
						$list[$index]->href	 			= App::url('@blog/id', ['id' => $item['post_id']], true).'#comment-'.$item['id'];
						//$list[$index]->href	 			= '@blog/'.$item['post_id'].'#comment-'.$item['id'];
						$list[$index]->id		 		= $item['post_id'];
						$index++;
						}
					}
				if (!empty($list)){

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
			'search.onContentSearchAreas'	=> ['onContentSearchAreas', 5],
			'search.onContentSearch'		=> ['onContentSearch', 5]
        ];

    }
	
	public function quote($text, $escape = true)
	{
		if (is_array($text))
		{
			foreach ($text as $k => $v)
			{
				$text[$k] = $this->quote($v, $escape);
			}

			return $text;
		}
		else
		{
			return '\'' . ($escape ? $this->escape($text) : $text) . '\'';
		}
	}

	// Query
	public function concatenateQuery($values, $separator = null)
	{
		if ($separator)
		{
			return 'CONCAT(' . implode(' || ' . $this->quote($separator) . ' || ', $values) . ')';
		}
		else
		{
			return 'CONCAT(' . implode(' || ', $values) . ')';
		}
	} 
	
	// MySql
	public function concatenate($values, $separator = null)
	{
		if ($separator)
		{
			$concat_string = 'CONCAT_WS(' . $this->quote($separator);

			foreach ($values as $value)
			{
				$concat_string .= ', ' . $value;
			}

			return $concat_string . ')';
		}
		else
		{
			return 'CONCAT(' . implode(',', $values) . ')';
		}
	}
	
	public function has_same_id($id, $r_array)
	{
		foreach ($r_array as $row)
		{
			if ($row['id'] == $id) return true;
		}
	return false;
	}
}