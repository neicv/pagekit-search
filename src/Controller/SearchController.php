<?php

namespace Friendlyit\Search\Controller;

use Pagekit\Application as App;

/**
 * @Access(admin=true)
 */
class SearchController 
{
	
	 /**
     * @Access("search: see search")
     * @Request({"filter": "array", "page":"int","interval":"string", "view":"string"})
     */
    public function statisticsAction($filter = null, $page = null, $interval = null)
    {
        return [
            '$view' => [
                'title' => __('Statistics'),
                'name'  => 'friendlyit/search:views/admin/keywords-index.php'
            ],
            '$data' => [

                'config'   => [
                    'filter' => (object) $filter,
                    'page'   => $page
                ]
            ]
        ];
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
	
	/**
	 * @Access("search: manage settings")
	 * @Route("/statistics/clear", name="statistics/clear", methods="DELETE") //, methods="DELETE")
	 * @Request({"interval":"string"},csrf=true)
     */
    public function clearAction($interval = null)
    {
        
		if (is_null($interval)) return ['message' => 'Empty request!'];
    	
    	$query =  App::db()->createQueryBuilder();
    	
		try
		{
			switch ($interval)
			{
				case 'yesterday':
					$query_time = '(putdate <(DATE(NOW()) - INTERVAL 1 DAY))';
					break;
			
				case 'week':
					$query_time = '(putdate <(DATE(NOW()) - INTERVAL 1 WEEK))';
					break;
			
				case 'month':
					$query_time = '(putdate <(DATE(NOW()) - INTERVAL 1 MONTH))';
					break;
				
				case '6month':
					$query_time = '(putdate <(DATE(NOW()) - INTERVAL 6 MONTH))';
					break;
					
				case 'year':
					$query_time = '(putdate <(DATE(NOW()) - INTERVAL 1 YEAR))';
					break;
		
				case 'all':
				default:
					$query_time = null; 
					break;
			}
			
			$query = $query->from('@search_keywords');
			if (!is_null($query_time))	$query->where($query_time);
			$count = $query->count();
    		$query->delete();
			
		}
		catch (\Exception $e) 
		{

			return ['message' => $e->getMessage()];
		}
	
        return ['message' => 'success', 'count' => $count];
    }
}
