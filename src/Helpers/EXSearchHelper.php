<?php
/**
 * @package     Pagekit Extension
 * @subpackage  Search content - EXSearchHelper
 *
 * @copyright   Copyright (C) 2016 Friendly-it, Inc. All rights reserved.
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Friendlyit\Search\Helpers;

use Friendlyit\Search\Helpers\EXTransliterate;
use Friendlyit\Search\Helpers\EXDefaultLocalise;
use Friendlyit\Search\Model\SearchKeywords;
use Pagekit\Application as App; 

/**
 * Search component helper.
 *
 * @since  0.1
 */

 
 
class EXSearchHelper
{
	
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  0.1
	 */
	protected 	$external_lang = true;
	
	/**
     * @var Default Time Zone
    */
	//protected 	$defaultTimeZone='UTC';
	
	 /**
     * @var Patch to dir w Language
     */
	protected 	static $patch;
		
	 /**
     * @class External Language Helpers 
     */
	protected	static $nameClass;
	
	
	
	public function __construct()
    {

		//if(date_default_timezone_get()!=$defaultTimeZone) date_default_timezone_set($defaultTimeZone);
		
		$lang = App::module('system')->config('site.locale');

		if (!$dir = $this->getPath()) {
			return $this->error(__('Invalid path.'));
		}
		
		if (!$dir = App::module('friendlyit/search')->get('path')) {
		return $this->error(__('Invalid path.'));
		}

		$path = $dir.'/filters/';
		

		
		//$external_lang = true;
		$lang2 = str_replace ('_','-',$lang);
		$ignoreFile = $path . $lang2 . '.localise.php';
		if (file_exists($ignoreFile))
			{
				require_once $ignoreFile;
			}
			else 
				{
				$lang2 	= 'en-GB';
				$lang	= 'En_GB';
				$ignoreFile = $path . $lang2 . '.localise.php';
				if (file_exists($ignoreFile))
					{
					require_once $ignoreFile;
					}
					//else $external_lang = false;
					else 	{
							$lang ='EXDefault';
							require_once $dir.'/src/Helpers/EXDefaultLocalise.php';
							}
				}
			// External Language alredy loaded in index.php autoload src = >
		$nameClass = $lang . 'Localise';
		self::$nameClass = $nameClass;
			

    }
	
	public static function getInstance()
    {
         return new self();
    }

	/**
	 * Sanitise search word.
	 *
	 * @param   string  &$searchword   Search word to be sanitised.
	 * @param   string  $searchphrase  Either 'all', 'any' or 'exact'.
	 *
	 * @return  boolean  True if search word needs to be sanitised.
	 */
	public static function santiseSearchWord(&$searchword, $searchphrase)
	{
 		$ignored = false;


		$AddHelper = self::$nameClass;
		$search_ignore = $AddHelper::getIgnoredSearchWords();
		
		// Check for words to ignore.
		$aterms = explode(' ', mb_strtolower($searchword));

		// First case is single ignored word.
		if (count($aterms) == 1 && in_array(mb_strtolower($searchword), $search_ignore))
		{	
			$ignored = true;
		}

		// Filter out search terms that are too small.
		$lower_limit = $AddHelper::getLowerLimitSearchWord();

		foreach ($aterms as $aterm)
		{
			if (strlen($aterm) < $lower_limit)
			{
				$search_ignore[] = $aterm;
			}
		}
		
		// Next is to remove ignored words from type 'all' or 'any' (not exact) searches with multiple words.
		if (count($aterms) > 1 && $searchphrase != 'exact')
		{
			$pruned     = array_diff($aterms, $search_ignore);
			$searchword = implode(' ', $pruned);
			//@deprecated  0.1.2
			//if (count($pruned) != count($aterms)){$ignored = true;};
		}
		//echo '</pre>';

		return $ignored;
	}

	/**
	 * Does search word need to be limited?
	 *
	 * @param   string  &$searchword  Search word to be checked.
	 *
	 * @return  boolean  True if search word should be limited; false otherwise.
	 *
	 * @since  0.1
	 */
	public static function limitSearchWord(&$searchword)
	{
		$restriction = false;

		$AddHelper = self::$nameClass;
		// Limit searchword to a maximum of characters.
		$upper_limit = $AddHelper::getUpperLimitSearchWord();
		$lower_limit = $AddHelper::getLowerLimitSearchWord();

		if (strlen($searchword) > $upper_limit)
		{
			$searchword  = substr($searchword, 0, $upper_limit - 1);
			$restriction = true;
		}

		// Searchword must contain a minimum of characters.
		if ($searchword && strlen($searchword) < $lower_limit)
		{
			$searchword  = '';
			$restriction = true;
		}

		return $restriction;
	}

	/**
	 * Prepares results from search for display.
	 *
	 * @param   string  $text        The source string.
	 * @param   string  $searchword  The searchword to select around.
	 *
	 * @return  string
	 *
	 * @since   0.1
	 */
	 
	public static function prepareSearchContent($text, $searchword)
	{
		// Strips tags won't remove the actual jscript.
		$text = preg_replace("'<script[^>]*>.*?</script>'si", "", $text);
		$text = preg_replace('/{.+?}/', '', $text);

		// $text = preg_replace('/<a\s+.*?href="([^"]+)"[^>]*>([^<]+)<\/a>/is','\2', $text);

		// Replace line breaking tags with whitespace.
		$text = preg_replace("'<(br[^/>]*?/|hr[^/>]*?/|/(div|h[1-6]|li|p|td))>'si", ' ', $text);

		return self::_smartSubstr(strip_tags($text), $searchword);
	}

	/**
	 * Checks an object for search terms (after stripping fields of HTML).
	 *
	 * @param   object  $object      The object to check.
	 * @param   string  $searchTerm  Search words to check for.
	 * @param   array   $fields      List of object variables to check against.
	 *
	 * @return  boolean True if searchTerm is in object, false otherwise.
	 */
	public static function checkNoHtml($object, $searchTerm, $fields)
	{
		
		$searchRegex = array(
			'#<script[^>]*>.*?</script>#si',
			'#<style[^>]*>.*?</style>#si',
			'#<!.*?(--|]])>#si',
			'#<[^>]*>#i'
		);
		$terms = explode(' ', $searchTerm);

		if (empty($fields))
		{
			return false;
		}

		foreach ($fields as $field)
		{
			if (!isset($object->$field))
			{
				continue;
			}

			$text = self::remove_accents($object->$field);

			foreach ($searchRegex as $regex)
			{
				$text = preg_replace($regex, '', $text);
			}

			foreach ($terms as $term)
			{
				$term = self::remove_accents($term);
				$term = mb_strtolower($term, 'UTF-8');
				$text = mb_strtolower($text, 'UTF-8');
				if (stristr($text, $term) !== false)
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Transliterates given text to ASCII.
	 *
	 * @param   string  $str  String to remove accents from.
	 *
	 * @return  string
	 *
	 * @since   0.1
	 */
	public static function remove_accents($str)
	{
		$str = EXTransliterate::utf8_latin_to_ascii($str); // !

		// @TODO: remove other prefixes as well?
		return preg_replace("/[\"'^]([a-z])/ui", '\1', $str);
		//return $str;
	}

	/**
	 * Returns substring of characters around a searchword.
	 *
	 * @param   string   $text        The source string.
	 * @param   integer  $searchword  Number of chars to return.
	 *
	 * @return  string
	 *
	 * @since   0.1
	 */
	public static function _smartSubstr($text, $searchword)
	{
		$AddHelper = self::$nameClass;
		$length      = $AddHelper::getSearchDisplayedCharactersNumber();
		$ltext       = self::remove_accents($text);
		$textlen     = strlen($ltext);
		$lsearchword = strtolower(self::remove_accents($searchword));
		$wordfound   = false;
		$pos         = 0;

		while ($wordfound === false && $pos < $textlen)
		{
			if (($wordpos = @strpos($ltext, ' ', $pos + $length)) !== false)
			{
				$chunk_size = $wordpos - $pos;
			}
			else
			{
				$chunk_size = $length;
			}

			$chunk     = substr($ltext, $pos, $chunk_size);
			$wordfound = strpos(strtolower($chunk), $lsearchword);

			if ($wordfound === false)
			{
				$pos += $chunk_size + 1;
			}
		}

		if ($wordfound !== false)
		{
			return (($pos > 0) ? '...&#160;' : '') . substr($text, $pos, $chunk_size) . '&#160;...';
		}
		else
		{
			if (($wordpos = @strpos($text, ' ', $length)) !== false)
			{
				return substr($text, 0, $wordpos) . '&#160;...';
			}
			else
			{
				return substr($text, 0, $length);
			}
		}
	}
	
	/**
	 * Returns the lower length limit of search words
	 *
	 * @return  integer  The lower length limit of search words.
	 *
	 * @since   0.1
	 */
	public static function getLowerLimitSearchWord()
	{
		$AddHelper = self::$nameClass;
		// Limit searchword to a minimum of characters.
		return $AddHelper::getLowerLimitSearchWord();
	}
	
	/**
	* Returns the upper length limit of search words
	*
	* @return  integer  The upper length limit of search words.
	*
	* @since   0.1
	*/
	public static function getUpperLimitSearchWord()
	{
		$AddHelper = self::$nameClass;
		// Limit searchword to a maxsimum of characters.
		return $AddHelper::getUpperLimitSearchWord();
	}
	
	/**
	*
	* @return  string translate Search Keyword № Result.
	*
	* @since   0.1
	*/
	public static function getPluralSearchKeywordNResult($total)
	{
		$AddHelper = self::$nameClass;
		$plural = $AddHelper::getPluralSuffixes($total);
		switch ($plural[0])
		{
			case '0':
				$s_result = __('Total: Nothing found.');
				break;

			case '1':
				$s_result = __('Total: %s result found.'); //объект
				break;
			
			case '2':
				$s_result = __('Total: %s result founds.'); //объекта
				break;
				
			case 'MORE':
				$s_result = __('Total: %s results found.'); // объектов
			default:
				$s_result = __('Total: %s results found.');
		}
		if ($total == 1)
			{
			$s_result = __('Total: One result found.');
			}
			
			$s_result = '<strong>'.$s_result.'</strong>';
			return $s_result;
	}



// --------------------------------------------------------------------------------------------------------------
	protected function getPath($path = '')
	{
		$root = strtr(App::path(), '\\', '/');
		$path = $this->normalizePath($root.'/'.App::request()->get('root').'/'.App::request()->get('path').'/'.$path);
	
		return 0 === strpos($path, $root) ? $path : false;
	}
	
	/**
	 * Normalizes the given path
	 *
	 * @param  string $path
	 * @return string
	 */
	protected function normalizePath($path)
	{
		$path   = str_replace(['\\', '//'], '/', $path);
		$prefix = preg_match('|^(?P<prefix>([a-zA-Z]+:)?//?)|', $path, $matches) ? $matches['prefix'] : '';
		$path   = substr($path, strlen($prefix));
		$parts  = array_filter(explode('/', $path), 'strlen');
		$tokens = [];
	
		foreach ($parts as $part) {
			if ('..' === $part) {
				array_pop($tokens);
			} elseif ('.' !== $part) {
				array_push($tokens, $part);
			}
		}
	
		return $prefix . implode('/', $tokens);
	}
	
	/**
	* Normalizes the given date 
	*
	* @param  string $format
	* @param  string $timestamp
	* @param  string $timezone
	* @return date
	*/
	public function _date($format="r", $timestamp=false, $timezone=false)
	{
		$userTimezone = new \DateTimeZone(!empty($timezone) ? $timezone : 'GMT');
		$gmtTimezone = new \DateTimeZone('GMT');
		$myDateTime = new \DateTime(($timestamp!=false?date("r",(int)$timestamp):date("r")), $gmtTimezone);
		$offset = $userTimezone->getOffset($myDateTime);
		return date($format, ($timestamp!=false?(int)$timestamp:$myDateTime->format('U')) + $offset);
	}
	/* Example */
	//echo 'System Date/Time: '.date("Y-m-d | h:i:sa").'<br>';
	//echo 'New York Date/Time: '._date("Y-m-d | h:i:sa", false, 'America/New_York').'<br>';
	//echo 'Belgrade Date/Time: '._date("Y-m-d | h:i:sa", false, 'Europe/Belgrade').'<br>';
	//echo 'Belgrade Date/Time: '._date("Y-m-d | h:i:sa", 514640700, 'Europe/Belgrade').'<br>';
	//echo 'Belgrade Date/Time: '._date("Y-m-d H:i:s", false, $localTimeZone);
	
	/**
	 * Logs a search term.
	 *
	 * @param   string  $search_term  The term being searched.
	 *
	 * @return  void
	 *
	 * @since   0.1
	 */
	
	public static function logSearch($search_term = null)
	{

		if (!is_null($search_term)){
			$SearchKeywords = SearchKeywords::create();
			$SearchKeywords->ip = ip2long(App::request()->getClientIp());
			//$SearchKeywords->putdate = EXSearchHelper::_date($putdate);
			$SearchKeywords->putdate = new \DateTime;
		
			$search_term = EXSearchHelper::strip_data(trim(mb_strtolower($search_term)));
			$search_term = stripslashes($search_term); 
			$search_term = htmlspecialchars($search_term);
			//$search_term =  App::db()->quote($search_term);
			//$search_term = mysql_escape_string($search_term);
			$SearchKeywords->word = $search_term;
			$SearchKeywords->save();
			return true;
		}
		return false;
	}
	
	/**
	* Method to escape a string for usage in an Search request.
	*
	* @param   string   $text   The string to be escaped.
	*
	* @return  string  The escaped string.
	*
	* @since   0.1
	*/
	
	public static function strip_data($text)
	{
		$quotes = array ("\x27", "\x22", "\x60", "\t", "\n", "\r", "*", "%", "<", ">", "?", "!" );
		$goodquotes = array ("-", "+", "#" );
		$repquotes = array ("\-", "\+", "\#" );
		$text = trim( strip_tags( $text ) );
		$text = str_replace( $quotes, '', $text );
		$text = str_replace( $goodquotes, $repquotes, $text );
		//$text = ereg_replace(" +", " ", $text);
		$text = str_replace(" +", " ", $text);
		return $text;
	}
}