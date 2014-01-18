<?php

namespace PhpGw2Api;

use PhpGw2Api\Cache;

/**
 * LICENSE: Permission is hereby granted, free of charge, to any person 
 * obtaining a copy of this software and associated documentation files 
 * (the "Software"), to deal in the Software without restriction, including 
 * without limitation the rights to use, copy, modify, merge, publish, 
 * distribute, sublicense, and/or sell copies of the Software, and to 
 * permit persons to whom the Software is furnished to do so, subject 
 * to the following conditions:
 * The above copyright notice and this permission notice shall be included in 
 * all copies or substantial portions of the Software.
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR 
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, 
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL 
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER 
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING 
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS 
 * IN THE SOFTWARE.
 *
 * @category	PhpGw2Api
 * @package		Service
 * @author		James McFadden <james@jamesmcfadden.co.uk>
 * @license		http://opensource.org/licenses/MIT	MIT
 * @version		0.1
 * @link		https://github.com/jamesmcfadden/PhpGw2Api
 * @see			https://forum-en.guildwars2.com/forum/community/api/API-Documentation
 */
class Service
{
	const	BASE_URI			= 'https://api.guildwars2.com',
			EVENT_URI			= '/events.json',
			EVENT_NAME_URI		= '/event_names.json',
			EVENT_DETAIL_URI	= '/event_details.json',
			MAP_NAME_URI		= '/map_names.json',
			WORLD_NAME_URI		= '/world_names.json',
			MATCH_URI			= '/wvw/matches.json',
			MATCH_DETAIL_URI	= '/wvw/match_details.json',
			OBJECTIVE_NAME_URI	= '/wvw/objective_names.json',
			ITEM_URI			= '/items.json',
			ITEM_DETAIL_URI		= '/item_details.json',
			RECIPE_URI			= '/recipes.json',
			RECIPE_DETAIL_URI	= '/recipe_details.json',
			GUILD_DETAIL_URI	= '/guild_details.json',
			BUILD_URI			= '/build.json',
			COLOUR_URI			= '/colors.json',
			CONTINENT_URI		= '/continents.json',
			MAP_URI				= '/maps.json',
			MAP_FLOOR_URI		= '/map_floor.json',
			FILE_URI			= '/files.json';
	
	/**
	 * Map method to their respective URIs
	 * 
	 * These will be used in __call when the method is not implemented
	 * and helps enforce DRY
	 * 
	 * @var array
	 */
	protected $_methodUriMap = array(
		'getEvents'			=> self::EVENT_URI,
		'getEventNames'		=> self::EVENT_NAME_URI,
		'getEventDetails'	=> self::EVENT_DETAIL_URI,
		'getMapNames'		=> self::MAP_NAME_URI,
		'getWorldNames'		=> self::WORLD_NAME_URI,
		'getMatches'		=> self::MATCH_URI,
		'getMatchDetails'	=> self::MATCH_DETAIL_URI,
		'getObjectiveNames' => self::OBJECTIVE_NAME_URI,
		'getItems'			=> self::ITEM_URI,
		'getItemDetails'	=> self::ITEM_DETAIL_URI,
		'getRecipes'		=> self::RECIPE_URI,
		'getRecipeDetails'	=> self::RECIPE_DETAIL_URI,
		'getGuildDetails'	=> self::GUILD_DETAIL_URI,
		'getBuild'			=> self::BUILD_URI,
		'getColours'		=> self::COLOUR_URI, // Correct spelling ^^
		'getColors'			=> self::COLOUR_URI,
		'getContinents'		=> self::CONTINENT_URI,
		'getMaps'			=> self::MAP_URI,
		'getMapFloor'		=> self::MAP_FLOOR_URI,
		'getFiles'			=> self::FILE_URI
	);
	
	/**
	 * Default cURL headers
	 * 
	 * @var array
	 */
	protected $_curlHeaders = array();
	
	/**
	 * Default GW2 API version
	 * 
	 * @var string
	 */
	protected $_versionNumber = 'v1';
	
	/**
	 * Return JSON objects as an associative array
	 * 
	 * @var boolean
	 */
	protected $_returnAssoc = true;
	
	/**
	 * If true, throws exceptions if there was a cURL error
	 * 
	 * @var boolean 
	 */
	protected $_throwCurlExceptions = true;
	
	/**
	 * Default cURL options
	 * 
	 * @var array
	 */
	protected $_curlOptions = array();
	
	/**
	 * cURL request information
	 * 
	 * @var array
	 */
	protected $_curlInfo = array();
	
	/**
	 * The result in it's original format
	 * 
	 * @var mixed
	 */
	protected $_result = null;
	
	/**
	 * Directory to save cached result in
	 * 
	 * @var string
	 */
	protected $_cacheDirectory = null;
	
	/**
	 * TTL for cache in seconds
	 * 
	 * @var integer
	 */
	protected $_cacheTtl = 3600;
	
	/**
	 * Whether or not to use HTTP pipelining
	 * 
	 * @var boolean
	 */
	protected $_pipeline = false;
	
	/**
	 * The request stack of URI endpoints
	 * 
	 * Only used if pipelining is set to true
	 * 
	 * @var boolean
	 */
	protected $_requestStack = array();
	
	/**
	 * Handle dependencies
	 */
	public function __construct($cachePath = null, $cacheTtl = 3600)
	{
		$this->setCacheDirectory($cachePath);
		$this->setCacheTtl($cacheTtl);
		$this->_handleDependencies();
	}
	
	/**
	 * Set the GW2 API version to be used
	 * 
	 * Return the current instance for a fluent interface
	 * 
	 * @param string $versionNumber
	 * @return \PhpGw2Api\Service
	 */
	public function setVersion($versionNumber)
	{
		if(is_numeric($versionNumber)) {
			$versionNumber = 'v' . $versionNumber;
		}
		$this->_version = $versionNumber;
		
		return $this;
	}
	
	/**
	 * Return details about a WvW match
	 * 
	 * @param array $parameters
	 * @param integer $ttl [optional] Override the default ttl for this request
	 * @return array
	 * @throws \Exception
	 */
	public function getMatchDetails(array $parameters, $ttl = null)
	{
		if(!array_key_exists('match_id', $parameters)) {
			throw new \Exception('match_id is required');
		}
		return $this->_processRequest(
			self::MATCH_DETAIL_URI, $parameters, $ttl
		);
	}
	
	/**
	 * Return details about an item
	 * 
	 * @param array $parameters
	 * @param integer $ttl [optional] Override the default ttl for this request
	 * @return array
	 * @throws \Exception
	 */
	public function getItemDetails(array $parameters, $ttl = null)
	{
		if(!array_key_exists('item_id', $parameters)) {
			throw new \Exception('item_id is required');
		}
		if(!is_numeric($parameters['item_id'])) {
			throw new \Exception('item_id must be a numeric value');
		}
		return $this->_processRequest(
			self::ITEM_DETAIL_URI, $parameters, $ttl
		);
	}
	
	/**
	 * Return details about a recipe
	 * 
	 * @param array $parameters
	 * @param integer $ttl [optional] Override the default ttl for this request
	 * @return array
	 * @throws \Exception
	 */
	public function getRecipeDetails(array $parameters, $ttl = null)
	{
		if(!array_key_exists('recipe_id', $parameters)) {
			throw new \Exception('recipe_id is required');
		}
		
		return $this->_processRequest(
			self::RECIPE_DETAIL_URI, $parameters, $ttl
		);
	}
	
	/**
	 * Return details about a guild
	 * 
	 * Either the guild ID or guild name is required
	 * 
	 * @param array $parameters
	 * @param integer $ttl [optional] Override the default ttl for this request
	 * @return array
	 * @throws \Exception
	 */
	public function getGuildDetails(array $parameters, $ttl = null)
	{
		if(!array_key_exists('guild_id', $parameters) &&
			!array_key_exists('guild_name', $parameters)) {
			throw new \Exception('guild_id or guild_name is required');
		}
		
		return $this->_processRequest(
			self::GUILD_DETAIL_URI, $parameters, $ttl
		);
	}
	
	/**
	 * Return details about a map floor
	 * 
	 * @param array $parameters
	 * @param integer $ttl [optional] Override the default ttl for this request
	 * @return array
	 * @throws \Exception
	 */
	public function getMapFloor(array $parameters, $ttl = null)
	{
		if(!array_key_exists('continent_id', $parameters) ||
			!array_key_exists('floor', $parameters)) {
			throw new \Exception('continent_id and floor are required');
		}
		
		return $this->_processRequest(
			self::MAP_FLOOR_URI, $parameters, $ttl
		);
	}
	
	/**
	 * Specify how the response is returned
	 * 
	 * If true JSON objects will be parsed into an associative array as opposed
	 * to stdClass
	 * 
	 * @param boolean $bool
	 * @return \PhpGw2Api\Service
	 */
	public function returnAssoc($bool)
	{
		$this->_returnAssoc = (bool) $bool;
		
		return $this;
	}
	
	/**
	 * Specify whether the class should throw curl exceptions
	 * 
	 * @param boolean $bool
	 * @return \PhpGw2Api\Service
	 */
	public function throwCurlExceptions($bool)
	{
		$this->_throwCurlExceptions = (bool) $bool;
		
		return $this;
	}
	
	/**
	 * Set custom cURL options
	 * 
	 * These will be merged with the defaults
	 * 
	 * @param array $options
	 * @return \PhpGw2Api\Service
	 */
	public function setCurlOptions(array $options)
	{
		$this->_curlOptions = $options;
		
		return $this;
	}
	
	/**
	 * Set custom cURL headers
	 * 
	 * These will be merged with the defaults
	 * 
	 * @param array $headers
	 * @return \PhpGw2Api\Service
	 */
	public function setCurlHeaders(array $headers)
	{
		$this->_curlHeaders = $headers + $this->_curlHeaders;
		
		return $this;
	}
	
	/**
	 * Set the path to the desired cache directory
	 * 
	 * @param string $directoryPath
	 * @return \PhpGw2Api\Service
	 */
	public function setCacheDirectory($directoryPath)
	{
		$this->_cacheDirectory = $directoryPath;
		
		return $this;
	}
	
	/**
	 * Set cache lifetime
	 * 
	 * @param integer $cacheTtl
	 * @return \PhpGw2Api\Service
	 */
	public function setCacheTtl($cacheTtl)
	{
		$this->_cacheTtl = $cacheTtl;
		
		return $this;
	}
	
	/**
	 * Set whether to use HTTP pipelining
	 * 
	 * @param boolean $bool
	 */
	public function initStack()
	{
		$this->_pipeline = true;
	}
	
	/**
	 * Return information about the cURL request
	 * 
	 * @return array
	 */
	public function getCurlInfo()
	{
		return $this->_curlInfo;
	}
	
	/**
	 * Execute a stack of URLs using HTTP pipelining
	 * 
	 * This method is only meant for use if pipelining is set to true. If it
	 * is called when pipelining is set to false an exception will be thrown
	 * 
	 * Once the stack has been processed it is reset
	 * 
	 * @return array
	 */
	public function executeStack()
	{
		if(!$this->_pipeline) {
			throw new \Exception('Call to executeStack when pipelining is set to false');
		}
				
		$this->_executeCurlPipeline($this->_requestStack);

		foreach($this->_result as $i => $r) {
			
			$this->_result[$i] = $this->_jsonDecode($r);
		}
		$this->_requestStack = array();
		
		return $this->_result;
	}
	
	/**
	 * Catch any method calls and attempt to resolve them
	 * 
	 * This makes use of the _methodUriMap array to find the API end point
	 * that should be used
	 * 
	 * @param string $method
	 * @param array $args
	 * @return array
	 * @throws \Exception
	 */
	public function __call($method, $args)
	{
		if(!array_key_exists($method, $this->_methodUriMap)) {
			throw new \Exception('Invalid method call ' . $method);
		}
		$params = (isset($args[0]) ? $args[0] : array());
		$ttl = (isset($args[1]) ? $args[1] : $this->_cacheTtl);
		
		return $this->_processRequest($this->_methodUriMap[$method], $params, $ttl);
	}
	
	/**
	 * Process a request
	 * 
	 * Either retrieve results from the cache or query the API itself. If pipelining is set
	 * to true, just register the URI on the stack and return null.
	 * 
	 * @param string $relativeUri Relative to BASE_URI
	 * @param array $parameters [optional]
	 * @param integer $ttl [optional]
	 * @return null|array
	 */
	protected function _processRequest($relativeUri, array $parameters = array(), $ttl = null)
	{
		if($this->_pipeline === true) {
			
			$this->_requestStack[] = $this->_buildRequestUri($relativeUri, $parameters);
			return;
		}
		
		$requestUri = $this->_buildRequestUri($relativeUri, $parameters);
		Cache::setDirectory($this->_cacheDirectory);
		
		if(!$this->_result = $this->_getCachedResult($requestUri)) {
			
			$this->_executeCurl($requestUri, $ttl);
			$this->_saveCache($requestUri, $this->_result, $ttl);
		}
		return $this->_jsonDecode($this->_result);
	}
	
	/**
	 * Execute a cURL request
	 * 
	 * @param string $requestUri
	 * @throws \Exception
	 */
	protected function _executeCurl($requestUri)
	{
		$ch = $this->_getCurlHandle($requestUri, $this->_curlOptions);

		$this->_result = curl_exec($ch);
		$this->_curlInfo = curl_getinfo($ch);

		if($this->_throwCurlExceptions 
			&& $this->_curlInfo['http_code'] !== 200 
			&& $this->_curlInfo['http_code'] !== 500) {
			throw new \Exception('cURL http_code ' . 
				$this->_curlInfo['http_code']);
		}
		curl_close($ch);
	}
	
	/**
	 * Execute multiple cURL requests in parallel
	 * 
	 * Searches for a cache for each endpoint in the stack. If
	 * no cache was found, a cURL multi handle is registered for
	 * execution
	 * 
	 * @param array $urls
	 */
	protected function _executeCurlPipeline(array $urls)
	{
		$this->_result = array();
		$curlHandles = array();

		foreach($urls as $i => $url) {
			if($cache = $this->_getCachedResult($url)) {
				$this->_result[$i] = $cache;
			} else {
				$curlHandles[$i] = $this->_getCurlHandle($url);
			}
		}

		$mh = curl_multi_init();
		
		foreach($curlHandles as $ch) {
			curl_multi_add_handle($mh,$ch);
		}
		
		do {
			curl_multi_exec($mh, $processing);
		} while($processing > 0);
		
		foreach($curlHandles as $i => $ch) {

			$result = curl_multi_getcontent($ch);
			curl_multi_remove_handle($mh, $ch);

			$this->_saveCache($urls[$i], $result);
			$this->_result[$i] = $result;
		}
		ksort($this->_result);

		curl_multi_close($mh);
	}
	
	/**
	 * Get a cURL handle with some default options set
	 * 
	 * Allows a consistent handle to be used in regular and pipeline
	 * requests
	 * 
	 * @param array $options
	 * @return resource
	 */
	protected function _getCurlHandle($url, array $options = array())
	{
		$handleOptions = $options + 
			array(
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_FAILONERROR => false,
				CURLOPT_CONNECTTIMEOUT => 10,
				CURLOPT_HEADER => false,
				CURLOPT_URL => $url,
				CURLOPT_REFERER => $url
			);

		$ch = curl_init();
		curl_setopt_array($ch, $handleOptions);
		
		return $ch;
	}
	
	/**
	* Save a cache if the cache directory is set
	* 
	* @param string $key
	* @param mixed $data
	* @param int $ttl [optional]
	*/
	protected function _saveCache($key, $data, $ttl = null)
	{
		if(null !== $this->_cacheDirectory) {
			$ttl = ($ttl ? $ttl : $this->_cacheTtl);
			return Cache::save($key, $data, $ttl);
		}
		return false;
	}

	/**
	 * Attempt to get a cache result based on a request URI
	 * 
	 * @param string $requestUri
	 * @return boolean
	 */
	protected function _getCachedResult($requestUri)
	{
		if(!Cache::hasCache($requestUri)) {
			return false;
		}
		return Cache::retrieve($requestUri);
	}
	
	/**
	 * Return a JSON decoded array
	 * 
	 * @param string $json
	 * @return array
	 */
	protected function _jsonDecode($json)
	{
		return (array) json_decode($json, $this->_returnAssoc);
	}
	
	/**
	 * Build the request URI
	 * 
	 * @param string $relativeUri
	 * @param array $parameters [optional]
	 * @return string
	 */
	private function _buildRequestUri($relativeUri, array $parameters = array())
	{
		$requestUri = self::BASE_URI . '/' . 
			$this->_versionNumber . $relativeUri;
		
		if(count($parameters) > 0) {
			$requestUri .= '?';
		}
		return $requestUri .= http_build_query($parameters);
	}
	
	/**
	 * Load in any dependencies here
	 * 
	 * Called upon instantiation
	 */
	private function _handleDependencies()
	{
		require_once __DIR__ . '/Cache.php';
	}
}