<?php
/**
 * @package     Pagekit Extension
 * @subpackage  Search.content - SiteController
 *
 * @copyright   Copyright (C) 2016 Friendly-it, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Friendlyit\Search\Controller;

use Pagekit\Application as App;
use Friendlyit\Search\Helpers\EXFilterInput;
use Friendlyit\Search\Helpers\EXSearchHelper;// as EXSearchHelper;
use Friendlyit\Search\Helpers\EXPagination;
use Friendlyit\Search\SearchExtension;
use Friendlyit\Search\Entity\Search;
use Friendlyit\Search\Event\SearchEvent;
use Pagekit\Framework\Controller\Controller;
use Pagekit\Framework\Controller\Exception;
use Pagekit\Extension\Extension;
//use Friendlyit\Search\Model\SearchKeywords;


/**
 * -Route("/search")
 */
class SiteController 
{
	/**
     * @var SearchExtension
     */
    protected $extension;
	
	/**
     * @var EXSearchHelper
     */
	protected $exsearchhelper;
	
	/**
	 * Search data array
	 *
	 * @var array
	 */
	protected $_data = null;
	
	/**
	 * Search key words
	 *
	 * @var string
	 */
	protected $_keyword = null;
	
	/**
	 * Search match expression
	 *
	 * @var string
	 */
	protected $_match = null;
	
	/**
	 * Search ordering
	 *
	 * @var string
	 */
	protected $_ordering = null;
	
	/**
	 * Search total
	 *
	 * @var integer
	 */
	protected $_total = null;

	/**
	 * Search areas
	 *
	 * @var integer
	 */
	protected  $_areas = null;

	/**
	 * Pagination object
	 *
	 * @var object
	 */
	protected $_pagination 	= null;
	
	 /**
	 * offset 
     * @var integer
     */
	
	protected $_limitstart 	= null;
	
	 /**
	 * Limit result per pages
     * @var integer
     */
	protected $_limit 		= null;
	
	 /**
	 * Log Searsh Enabled
     * @var bool
     */
	protected $_enable_log_searches = false;
	
	protected $AdditionalUrlParam = null;
	
	
	// *************************************************
	
    /**
     * Constructor.
     * @param SearchExtension $extension
     */
    public function __construct()
    {
        $this->extension = App::module('friendlyit/search');
		//$this->EXSearchHelper = new EXSearchHelper();
        $this->exsearchhelper = EXSearchHelper::getInstance();  
    }

	
    /**
	* @Route("/", name="site") 
	* @Route(methods="GET")
    */
    public function indexAction()

    {	
		//$lang  = $this['option']->get('system:app.locale');
		$squery		= null;
		$msg_error 	= null;
		$error		= false;
		$rows    	= null;
		$results 	= null;
		$ordering	= null;
		$total   	= 0;
		
		// Get some data from the model
		
		// Get parameters.
		
		// 'advanced' - parameters owned by advanced form of search		
		$params = $this->extension->config('advanced');	
		// Get switсh Search Statistics Enable
		$this->_enable_log_searches	= isset($params['statistics_enabled']) ? $params['statistics_enabled'] : false ;
		
		// 'defaults' - parameters owned by default form of search
		$params = $this->extension->config('defaults'); 
		
		// Get the pagination request variables
		$this->_limit = (!$params['result_per_page']) ? 15 : $params['result_per_page'];
		$this->_limitstart = '0';



		
		$searchphrase = 'all';
		
		// Set the search parameters

		$areas = null;

		$squery = App::url()->current();
		$squery = trim($squery);
		$squery = stripslashes($squery); 
		$squery = preg_replace("#(/\*([^*]|[\r\n]|(\*+([^*/]|[\r\n])))*\*+/)|([\s\t]//.*)|(^//.*)#", '', $squery);
		$squery = parse_url($squery);
		if(array_key_exists('query', $squery)) {
			$squery = $squery['query'];
			parse_str($squery, $squery);
			}
		else $squery= array();
		
		$this->AdditionalUrlParam = $squery; 
		unset($this->AdditionalUrlParam['limitstart']);
		
		$searchword 	= 	$this->getIfSet($squery['searchword']);	
		$ordering		=	$this->getIfSet($squery['ordering']);
		$searchphrase	=	$this->getIfSet($squery['searchphrase']);
		$limit			=	$this->getIfSet($squery['limit']);
		$limitstart		=	$this->getIfSet($squery['limitstart']);
		$type			=	$this->getIfSet($squery['type']);
		$tmpl			=	$this->getIfSet($squery['tmpl']);
		$itemid			=	$this->getIfSet($squery['itemid']);
		$areas			=	$this->getIfSet($squery['areas']);
		
		//Set the search areas
		$this->setAreas($areas);
		$areas = $this->getAreas();			
			
		// ---- If is autocomplete literally Widget search	----:
		
		if ($type == 'json' && $tmpl == 'raw') {
	
			if(!empty($_POST['searchword'])){
				
				// slashes cause errors, <> get stripped anyway later on. # causes problems.
				$badchars = array('#', '>', '<', '\\'); //$search['searchword']
				$search_word = trim(str_replace($badchars, '', (string)$_POST['searchword']));
		
				// if searchword enclosed in double quotes, strip quotes and do exact match
				if (substr($search_word, 0, 1) == '"' && substr($search_word, -1) == '"'){
					
					$searchword = substr($search_word, 1, -1);
					$searchphrase =  'exact';
					}
				else
				{
					$searchword = $search_word;
				}
					
					
			}
		}
	
			
		if (!in_array($searchphrase,['all', 'any' , 'exact'])){
			$searchphrase = 'all';}
		$match	=	$searchphrase;
	
		if (!in_array($ordering,['newest', 'oldest', 'popular', 'alpha', 'category'])){
			$ordering	= 'newest';}
			
			
		// Log the search
		//EXSearchHelper::logSearch($searchword, 'simple_search');

		// Limit searchword

		//$EXSearchHelper = new EXSearchHelper();

		$upper_limit = $this->exsearchhelper->getUpperLimitSearchWord();
		$lower_limit = $this->exsearchhelper->getLowerLimitSearchWord();
		
		$upper_limit = (int)((!$upper_limit) ? 200 : $upper_limit);
		
		if ($this->exsearchhelper->limitSearchWord($searchword))
			{
				$s = __('Search term must be a minimum of %1$s characters and a maximum of %2$s characters.');
				$msg_error = sprintf($s, $lower_limit, $upper_limit);
				$error = true;
			}

		// Sanitise searchword
		if ($this->exsearchhelper->santiseSearchWord($searchword, $match))
			{
				$msg_error = __('One or more common words were ignored in the search.');
			}

		// Put the filtered results back into the model
		$this->_keyword = $searchword;
		
		// Statistics Search
		if ((!$error) && $this->_enable_log_searches)
			{
				EXSearchHelper::logSearch($searchword);
			}
		
		// Built select lists
		$searchphrase    = (!$searchphrase ) ? 'all': $searchphrase;
		$ordering	     = (!$ordering ) ? null: $ordering; /// ????????????????????????????????? null not newest ??
		$limit			 = (!$limit ) ? $this->_limit : $limit;
		$limitstart			 = (!$limit ) ? $this->_limitstart : $limitstart;
		$this->_limit = $limit;
		$this->_limitstart = $limitstart; 

		// Set the search parameters
		$this->setSearch($searchword, $match, $ordering);
		
		// Built select lists
		$orders   				= array();
		$searchphrases         	= array();
		$lists 		            = array();
		$lists['searchkeywordnresult'] = null;
		$meta_id				= 'searchphrase';
		
		$html_2 ='';
		foreach (['all' => __('All words'), 'any' => __('Any words'), 'exact' => __('Exact Phrase')] as $key => $name)
			{
			$html 					= null;
			$html	 = "<label class=\"radio\" id=\"".$meta_id ."".$key . "-lbl\" for=\"" . $meta_id . "all\">";	
			$html	.= "<INPUT name=\"search[" . $meta_id . "]\" id=\"". $meta_id ."".$key ."\" type=\"radio\" v-model=\"search.searchphrase\" value=\"".$key ."\"";
			if ($key == $searchphrase) {$html	.= " checked";}
			$html	 		       .= ">&nbsp". $name . "</label> &nbsp";
			$searchphrases[]        = $html;	
			$html_2 .= $html;
			}
		$lists['searchphrase']	= $html_2 ;
		
		$html	 = "<SELECT name=\"search[ordering]\" class=\"inputbox\" id=\"ordering\" v-model=\"search.ordering\">";
		foreach (['newest' => __('Newest First'), 'oldest' => __('Oldest First'), 'popular' => __('Most Popular'), 'alpha' => __('Alphabetical'), 'category' => __('Category')] as $key => $name)
			{
			$html_2 	 = null;	
			$html_2	 	.= "<OPTION ";
			if ($key == $ordering) {$html_2	.= "selected=\"selected\"";}
			$html_2		.= " value=\"".$key . "\">". $name . "</OPTION>";
			$orders[] 	 = $html_2;
			$html 		.= $html_2;
			}
		$html	.= "</SELECT>";
		$lists['ordering'] = $html;
		
		if (!$error)
		// do search
		{
			$results    = $this->getData();
			$total      = $this->getTotal();
			$pagination = $this->getPagination();
			
			// ---- If is autocomplete literally Widget search	----:	
			if ($type == 'json' && $tmpl == 'raw') {	

				// set defaults
				$data = array();
				# get settings
				$itemid	= (int)((!$itemid) ? null : $itemid);
				if ($itemid){
					$settings = $this->getWidgetSettings($itemid);
					$settings = (!empty($settings->data)) ? json_decode($settings->data, true) : array();

					# push retrieved settings
					foreach ($settings as $key => $value)
						{
							$data[$key] = $value;
						}
					$res_limit  = (int)((!$data['result_per_page']) ? 6 : $data['result_per_page']);
					$char_limit = (int)((!$data['char_limit']) ? 110 : $data['char_limit']);
					}
				else {
					$res_limit  = 6;	
					$char_limit = 110;
					}
				
				//search results
				$res_items = array();
				if (!$error && count($results) > 0) {
					foreach ($results as $res) {
						
						// strip text
						$text = str_replace(array("\r\n", "\n", "\r", "\t"), " ", $res->text);
						$text = html_entity_decode($text, ENT_COMPAT, 'UTF-8');
						$text = preg_replace('/{.+?}/', '', $text);
						$text = substr(trim(strip_tags($text)), 0, $char_limit);

						// create item
						$item          = array();
						$item['title'] = '<div class="uk-text-bold">'. $res->title . '</div>';
						$item['text']  = substr_replace($text, '...', strrpos($text, ' '));
						$item['url']   = App::url($res->href);
						$res_items[]   = $item;
						
					}
				}	
				
				if (App::request()->isXmlHttpRequest()) 
					{
						return App::response()->json(array('results' => array_slice($res_items, 0, $res_limit), 'count'=> count($results), 'error' => $msg_error));
					}
			}
			
			else 
			{	
				$lists['searchkeywordnresult'] = $this->exsearchhelper->getPluralSearchKeywordNResult($total); 

				for ($i = 0, $count = count($results); $i < $count; $i++)
				{
					$row = & $results[$i]->text;
					
					if ($match == 'exact')
					{
						$searchwords = array($searchword);
						$needle      = $searchword;
					}
					else
					{
						$searchworda = preg_replace('#\xE3\x80\x80#s', ' ', $searchword);
						$searchwords = preg_split("/\s+/u", $searchworda);
						$needle      = $searchwords[0];
					}
					
					$row          = EXSearchHelper::prepareSearchContent($row, $needle);
					$searchwords  = array_values(array_unique($searchwords));
					$srow         = mb_strtolower(EXSearchHelper::remove_accents($row), 'UTF-8');  // !!!!!!!!!!!!!!!!!!!!!!!!
					$hl1          = '<span class="uk-text-bold uk-text-success">';// uk-text-primary">';//= '<span class="highlight">';
					$hl2          = '</span>';
					$posCollector = array();
					$mbString     = extension_loaded('mbstring');  // !!!
					
					if ($mbString)
					{
						// E.g. german umlauts like Г¤ are converted to ae and so
						// $pos calculated with $srow doesn't match for $row
						$correctPos     = (mb_strlen($srow) > mb_strlen($row));
						$highlighterLen = mb_strlen($hl1 . $hl2);
						
					}
					else
					{
						// E.g. german umlauts like Г¤ are converted to ae and so
						// $pos calculated with $srow desn't match for $row
						$correctPos     = (strlen($srow) > strlen($row));
						$highlighterLen = strlen($hl1 . $hl2);
					}
					
					foreach ($searchwords as $hlword)
					{
						if ($mbString)
						{
							if (($pos = mb_strpos($srow, mb_strtolower(EXSearchHelper::remove_accents($hlword), 'UTF-8'))) !== false) // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
							{
								// Iconv transliterates 'в‚¬' to 'EUR'
								// TODO: add other expanding translations?
								$eur_compensation = $pos > 0 ? substr_count($row, "\xE2\x82\xAC", 0, $pos) * 2 : 0;
								$pos              -= $eur_compensation;

								if ($correctPos)
								{
									// Calculate necessary corrections from 0 to current $pos
									$ChkRow     = mb_substr($row, 0, $pos);
									$sChkRowLen = mb_strlen(mb_strtolower(EXSearchHelper::remove_accents($ChkRow), 'UTF-8'));
									$ChkRowLen  = mb_strlen($ChkRow);
									// correct $pos
									$pos -= ($sChkRowLen - $ChkRowLen);
								}

								// Collect pos and searchword
								$posCollector[$pos] = $hlword;
							}
						}
						else
						{
							if (($pos =strpos($srow, strtolower(EXSearchHelper::remove_accents($hlword)))) !== false)
							{
								// iconv transliterates 'в‚¬' to 'EUR'
								// TODO: add other expanding translations?
								$eur_compensation = $pos > 0 ? substr_count($row, "\xE2\x82\xAC", 0, $pos) * 2 : 0;
								$pos              -= $eur_compensation;

								if ($correctPos)
								{
									// Calculate necessary corrections from 0 to current $pos
									$ChkRow     = substr($row, 0, $pos);
									$sChkRowLen = strlen(strtolower(EXSearchHelper::remove_accents($ChkRow)));
									$ChkRowLen  = strlen($ChkRow);
									// Correct $pos
									$pos -= ($sChkRowLen - $ChkRowLen);
								}

								// Collect pos and searchword
								$posCollector[$pos] = $hlword;
							}
						}
					}

					if (count($posCollector))
					{
						// Sort by pos. Easier to handle overlapping highlighter-spans
						ksort($posCollector);
						$cnt                = 0;
						$lastHighlighterEnd = -1;

						foreach ($posCollector as  $pos => $hlword)
						{
							$pos += $cnt * $highlighterLen;
							// Avoid overlapping/corrupted highlighter-spans
							// TODO $chkOverlap could be used to highlight remaining part
							// of searchword outside last highlighter-span.
							// At the moment no additional highlighter is set.
							$chkOverlap = $pos - $lastHighlighterEnd;

							if ($chkOverlap >= 0)
							{
								// Set highlighter around searchword
								if ($mbString)
								{
									$hlwordLen = mb_strlen($hlword);
									$row       = mb_substr($row, 0, $pos) . $hl1 . mb_substr($row, $pos, $hlwordLen) . $hl2 . mb_substr($row, $pos + $hlwordLen);
								}
								else
								{
									$hlwordLen = strlen($hlword);
									$row       = substr($row, 0, $pos) . $hl1 . substr($row, $pos, strlen($hlword)) . $hl2 . substr($row, $pos + strlen($hlword));
								}

								$cnt++;
								$lastHighlighterEnd = $pos + $hlwordLen + $highlighterLen;
							}
						}
					}
					$result = & $results[$i];

					if (!$result->created) {$result->created = '';}
					$result->count   = $i + 1;
				}
			}
		}

		$this->_total         	= $total;
		$this->_pagination   	= &$pagination;
		
		// return normal view search form

		return [
			'$view' => [
				'title'		 => __('Search'),
				'name' => 'friendlyit/search:views/form/placeholder.php',
				//'name' => 'search:views/form/placeholder.php',
				'name' => 'friendlyit/search/form/placeholder.php',
				],
			'posts'				=> [], 
			'searchword'		=>  $searchword,
			'results'			=>  &$results,
			'origkeyword'		=>  $searchword,
			'error'				=>  $msg_error,
			'total'				=>  $total,
			'lists'				=>	&$lists,
			'searchareas'		=>	$areas,
			'limit'				=>	$limit,
			'pagination'   		=>  &$pagination,
			'upper_limit'		=>  $upper_limit,
			'params'			=>  $params
			];
			
    }

    /**
	* @Route("/submit", name="submit")
    * @Request({"search": "array"}, csrf=true)
	* @Route(methods="POST");
    */
    public function submitAction($search  = '')
    {
	try {
			if (!App::csrf()->validate()) {
                throw new Exception(__('Invalid token. Please try again.'));
				            return App::redirect('@search/site');
            }
		
		// slashes cause errors, <> get stripped anyway later on. # causes problems.
		$badchars = array('#', '>', '<', '\\'); //$search['searchword']
		$searchword = trim(str_replace($badchars, '', $search['searchword']));
		
		// if searchword enclosed in double quotes, strip quotes and do exact match
		if (substr($searchword, 0, 1) == '"' && substr($searchword, -1) == '"')
		{
			$post['searchword'] = substr($searchword, 1, -1);
			$search['searchphrase'] =  'exact';
		}
		else
		{
			$post['searchword'] = $searchword;
		}
		
		$post['ordering']    	=  $this->getIfSet($search['ordering']);
		$post['searchphrase']	=  $this->getIfSet($search['searchphrase']);
		if ($post['searchphrase'] == null) {$post['searchphrase'] = 'all';}
		$areas     				=  $this->getIfSet($search['areas']);

		if ($areas)
		{
			foreach ($areas as $area)
			{

				$post['areas'][] = EXFilterInput::getInstance()->clean($area, 'cmd');

			}
		}	
		$post['limit'] 			=  $this->getIfSet($search['limit']);
		
		if ($post['limit'] === null)
		{
			unset($post['limit']);
		}
		
		unset($post['task']);
		unset($post['submit']);
		//$post['submit'] = true;
		//unset($post['csrf']);
		
		return App::redirect('@search/site', $post);


        } catch (\Exception $e) {

            if (!$this['request']->isXmlHttpRequest()) {
                foreach ($errors as $error) {
                    $this['message']->error($error);
                }
                return App::redirect('@search/site');
         
            }
			App::message()->error($e->getMessage());

            return App::redirect('@search/site');

        }
    }
	
	
	// *****************************	Helpers		********************
	
	public function setQuery($query)
	{
		if (is_array($query))
		{
			$this->vars = $query;
		}
		else
		{
			if (strpos($query, '&amp;') !== false)
			{
				$query = str_replace('&amp;', '&', $query);
			}

			parse_str($query, $this->vars);
		}

		// Empty the query
		$this->query = null;
	}
	
	/**
	 * Method to set the search parameters
	 *
	 * @access	public
	 * @param string search string
	 * @param string mathcing option, exact|any|all
	 * @param string ordering option, newest|oldest|popular|alpha|category
	 */
	
	public function setSearch($keyword, $match = 'all', $ordering = 'newest')
	{
		if (isset($keyword))
		{
			$this->origkeyword = $keyword;
			if ($match !== 'exact')
			{
				$keyword = preg_replace('#\xE3\x80\x80#s', ' ', $keyword);
			}

			$this->_keyword = $keyword;
		}

		if (isset($match))
		{
			$this->_match = $match;
		}

		if (isset($ordering))
		{
			$this->_ordering = $ordering;
		}
	}
	
	
	
	/**
	 * Method to set the search areas
	 *
	 * @access	public
	 * @param   array  Active areas
	 * @param   array  Search areas
	 */
	public function setAreas($active = array(), $search = array())
	{
		$this->_areas['active'] = $active;
		$this->_areas['search'] = $search;
	}
	
	/**
	 * Method to get the search areas
	 *
	 * @since 0.1
	 */
	public function getAreas()
	{	
		// Load the Category data
		if (empty($this->_areas['search']))
		{
			$areas = array();
			$null_array = array();
			$searchareas = App::trigger(new SearchEvent('search.onContentSearchAreas'))->getSearchArray(array());
			
			foreach ($searchareas as $area)
			{
				if (is_array($area))
				{
					$areas = array_merge($areas, $area);
				}
			}

			$this->_areas['search'] = $areas;
		}
		return $this->_areas;
	}
	
	/**
	 * Method to get weblink item data for the category
	 *
	 * @access public
	 * @return array
	 */
	public function getData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$areas = $this->getAreas();
			$results = App::trigger(new SearchEvent('search.onContentSearch', array	($this->_keyword, $this->_match, $this->_ordering, $areas['active'])))->getSearchData();
			
			$rows = array();
			foreach ($results as $result)
			{
				$rows = array_merge((array) $rows, (array) $result);
			}

			$this->_total	= count($rows);

			if ($this->_limit > 0)
			{
				$this->_data	= array_splice($rows, $this->_limitstart, $this->_limit);
			} else {
				$this->_data = $rows;
			}
		}
			
		return $this->_data;
	}
	
	/**
	 * Method to get the total number of weblink items for the category
	 *
	 * @access public
	 * @return  integer
	 */
	public function getTotal()
	{
		return $this->_total;
	}

	/**
	 * Method to get a pagination object of the weblink items for the category
	 *
	 * @access public
	 * @return  integer
	 */
	public function getPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_pagination))
		{
			$prefix = '';
			$route = App::url('@search/site');
			$this->_pagination = new EXPagination($this->getTotal(), $this->_limitstart, $this->_limit, $prefix, $this->AdditionalUrlParam, $route);

		}

		return $this->_pagination;
	}
	
	public function getLimit()
    {
        return $this->_limit;
    }

    public function setLimit($limit)
    {
        $this->_limit = $limit;
    }
	
	private function getWidgetSettings($id = 0)
    {
        $prefix = App::db()->getPrefix();
        $settings = App::db()->fetchObject('SELECT data FROM ' . $prefix . 'system_widget WHERE id = ' . $id);

        return (!empty($settings)) ? $settings : null;
    }
    
    public function getIfSet(& $var) {
    	if (isset($var)) {
    		return $var;
    	}
    	return null;
    }

}