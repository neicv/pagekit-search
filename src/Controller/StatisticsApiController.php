<?php

namespace Friendlyit\Search\Controller;

use Pagekit\Application as App;


/**
 * @Access("search: see search")
 * @Route("statistics", name="statistics")
 */
class StatisticsApiController
{
	/**
     * @Route("/", methods="GET")
     * @Request({"filter": "array", "page":"int", "interval":"string", "view":"string"})
     */
    public function indexAction($filter = [], $page = 0, $interval = 'today', $view = 'all')
    {
    	$limit = 25;
    	// Get Seze DB in KB And MB
    	$prefix = App::db()->getPrefix();
    	$name = $prefix.'search_keywords'; 
    	$db_name = App::db()->executeQuery('select database()')->fetchColumn();
    	$query = App::db()->executeQuery('SELECT data_length+index_length AS len_bytes, round(((data_length + index_length) / 1024 / 1024), 2) \'len_mb\' FROM information_schema.tables where table_name = \''.$name.'\' AND table_schema = \''.$db_name.'\'');
    	$db_len = $query->fetch();
    	
    	
    	switch ($interval)
    	{
    		case 'yesterday':
    			$query_time = '(putdate >= (INTERVAL -1 DAY + curdate()) AND putdate < CURDATE())';
    			break;
    	
    		case 'week':
    			//$query_time = '(putdate > DATE_SUB(CURDATE(), INTERVAL (DAYOFWEEK(CURDATE()) -1) DAY) AND putdate < DATE_ADD(CURDATE(), INTERVAL (9 - DAYOFWEEK(CURDATE())) DAY))';
    			$query_time = '(putdate >=(DATE(NOW()) - INTERVAL 1 WEEK))';
    			break;
    	
    		case 'month':
    			//$query_time = '(putdate > LAST_DAY(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))AND putdate < DATE_ADD(LAST_DAY(CURDATE()), INTERVAL 1 DAY))';
    			$query_time = '(putdate >=(DATE(NOW()) - INTERVAL 1 MONTH))';
    			break;
				
			case 'year':
    			//$query_time = '(putdate > LAST_DAY(DATE_SUB(CURDATE(), INTERVAL 1 YEAR))AND putdate < DATE_ADD(LAST_DAY(CURDATE()), INTERVAL 1 DAY))';
    			$query_time = '(putdate >=(DATE(NOW()) - INTERVAL 1 YEAR))';
    			break;
 	
    		case 'today':
    		default:
    			$query_time = '(putdate >= CURDATE())'; 
				//$query_time = '(putdate >=(DATE(NOW()) - INTERVAL 1 MONTH))';
    			break;
    	}

		$filter = array_merge(array_fill_keys(['search', 'order', 'order2', 'limit'], ''), $filter);

		extract($filter, EXTR_SKIP);

		if(!App::user()->hasAccess('search: see search')) {
			//$author = App::user()->id;
			return "Deny Access";
		}

		
		//$skip_date = !preg_match("/[^0-9_ :-]/",$search);// " Date  == true" ;
		$search = stripslashes($search);
		$search = htmlspecialchars($search);
		//$search = mysql_escape_string($search);
		

		if ($view == 'all')
    	{
			$query1 = App::db()->createQueryBuilder();

			$query1->from('@search_keywords')
					->select(['word', 'INET_NTOA(ip) as ip', 'putdate'])
					->where($query_time);


			if ($search) {
				$query1->where(function ($query1) use ($search) {
					if (!preg_match("/[^0-9_ :-]/",$search)) {$query1->orWhere(['word LIKE :search', 'INET_NTOA(ip) LIKE :search', 'putdate LIKE :search'], ['search' => "%{$search}%"]);
							
					}
					else {$query1->orWhere(['word LIKE :search', 'INET_NTOA(ip) LIKE :search'], ['search' => "%{$search}%"]);
					
					}
				});
			}

			if (!preg_match('/^(putdate|word|ip)\s(asc|desc)$/i', $order, $order)) {
				$order = [1 => 'putdate', 2 => 'desc'];
			}

			
			$count = $query1->count();
			$pages = ceil($count / $limit);
			$page  = max(0, min($pages - 1, $page));

			$keywords 	= array_values($query1->offset($page * $limit)->limit($limit)->orderBy($order[1], $order[2])->get());
		}
		else
		{
			$query3 = App::db()->createQueryBuilder();
			$query3->select('COUNT(*) AS qcount FROM (SELECT word, putdate, COUNT(word) AS wcount')
				->from('@search_keywords')
				->where($query_time);
		
			if (!$search) {
				$query3->groupBy('word ) AS qend');
				}
				else 
				{
				$query3->groupBy('word');
				$query3->having('((word LIKE \'%'.$search.'%\' OR COUNT(word) LIKE \'%'.$search.'%\') AND '.$query_time. ')) AS qend');
			}
			

			$count= array_values($query3->get(\PDO::FETCH_COLUMN));
			$count= $count[0]['qcount'];
			
			$query2 = null;
			$query2 = App::db()->createQueryBuilder();
			$query2->select('word', 'putdate','COUNT(word) AS wcount')
				->from('@search_keywords')
				->where($query_time)
				->groupBy('word');
			
			if ($search) {
				
				$query2->having('((word LIKE \'%'.$search.'%\' OR COUNT(word) LIKE \'%'.$search.'%\') AND '.$query_time. ')');
			}
			
			if (!preg_match('/^(word|wcount)\s(asc|desc)$/i', $order2, $order2)) {
				$order2 = [1 => 'wcount', 2 => 'desc'];
			}
	
			$pages = ceil($count / $limit);
			$page  = max(0, min($pages - 1, $page));
			
			$query2 = $query2->offset($page * $limit)->limit($limit)->orderBy($order2[1], $order2[2]);
			if ($order2[1]=='wcount') {
				$query2 = $query2->orderBy('word', 'asc');
			}
			$keywords 	= array_values($query2->get());
		}
		
        return compact('keywords', 'pages', 'count', 'db_len');
    }
}