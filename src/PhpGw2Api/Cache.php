<?php

namespace PhpGw2Api;

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
 * @package		Cache
 * @author		James McFadden <james@jamesmcfadden.co.uk>
 * @license		http://opensource.org/licenses/MIT	MIT
 * @version		0.1
 * @link		https://github.com/jamesmcfadden/PhpGw2Api
 * @see			https://forum-en.guildwars2.com/forum/community/api/API-Documentation
 */
class Cache
{
	const NEWLINE_CHAR = "\n";
	
	/**
	 * @var string
	 */
	static protected $_path;
	
	/**
	 * Set the base cache directory
	 * 
	 * @param string $path
	 */
	static public function setDirectory($path)
	{
		self::$_path = $path;
	}
	
	/**
	 * Attempt to save a cache file
	 * 
	 * The expiry timestamp is calculated and prepended to the content
	 * so it can be queried upon retrieval
	 * 
	 * @param string $key
	 * @param mixed $content
	 * @param integer $ttl [optional[ Time to live in seconds, defaults to one day
	 * @throws \Exception
	 * @return integer|boolean
	 */
	static public function save($key, $content, $ttl = 3600)
	{
		if(!is_readable(self::$_path)) {
			if(!mkdir(self::$_path, 0777, true)) {
				throw new \Exception(self::$_path . 
					' is not readable for caching and could not be created');
			}
		}
		$expiry = time() + (int) $ttl;
		$cacheContent = $expiry . self::NEWLINE_CHAR . serialize($content);
		$path = self::_getCachePath($key);
		
		try {
			$fh = fopen($path, 'w');
			fwrite($fh, $cacheContent);
			fclose($fh);
		} Catch(Exception $e) {
			throw new Exception('Unable to write to cache file ' . $path . 
				'. Check the file exists and has the correct permissions');
		}
	}
	
	/**
	 * Retrieve a cache
	 * 
	 * Check the cache exists, remove the expiry date and
	 * unserialize the content
	 * 
	 * Returns false if no cache item was found
	 * 
	 * @param string $key
	 * @return boolean|string
	 */
	static public function retrieve($key)
	{
		if(self::hasCache($key)) {
			
			$path = self::_getCachePath($key);
			
			$fh = fopen($path, 'r');
			$content = fread($fh, filesize($path));
			$lines = explode(self::NEWLINE_CHAR, $content);
			array_shift($lines);
			
			return unserialize(implode(self::NEWLINE_CHAR, $lines));
		}
		return false;
	}
	
	/**
	 * Check if a cache exists and is valid
	 * 
	 * @param string $key
	 * @return boolean
	 */
	static public function hasCache($key)
	{
		$path = self::_getCachePath($key);
	
		if(is_readable($path)) {
			
			$fh = fopen($path, 'r');
			$expiry = (int) trim(fgets($fh));
			fclose($fh);
			
			if($expiry < time()) {
				return false;
			}
			return true;
		}
		return false;
	}
	
	/**
	 * Return an absolute cache path
	 * 
	 * Path is based on the predefined cache directory and the
	 * key for the relevant cache file
	 * 
	 * @param string $key
	 * @return string
	 */
	static private function _getCachePath($key)
	{
		return self::$_path . DIRECTORY_SEPARATOR . md5($key) . '.cache';
	}
}