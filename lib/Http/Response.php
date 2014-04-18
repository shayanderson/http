<?php
/**
 * HTTP Request/Response Package for PHP 5.4+
 *
 * @package HTTP
 * @version 1.0 - Apr 18, 2014
 * @copyright 2014 Shay Anderson <http://www.shayanderson.com>
 * @license MIT License <http://www.opensource.org/licenses/mit-license.php>
 * @link <https://github.com/shayanderson/http>
 */
namespace Http;

/**
 * HTTP Response Class
 *
 * @author Shay Anderson 04.14 <http://www.shayanderson.com/contact>
 */
class Response
{
	/**
	 * Response codes
	 */
	const CODE_OK = 200;

	/**
	 * Response headers
	 *
	 * @var array
	 */
	private $__headers = [];

	/**
	 * HTTP response code
	 *
	 * @var int
	 */
	private $__response_code;

	/**
	 * Response string
	 *
	 * @var string
	 */
	private $__str;

	/**
	 * Request elapsed time
	 *
	 * @var float
	 */
	private $__time_elapsed;

	/**
	 * Request URL
	 *
	 * @var string
	 */
	private $__url;

	/**
	 * Error string (when error occurs)
	 *
	 * @var string
	 */
	public $error;

	/**
	 * Error occurred flag
	 *
	 * @var boolean
	 */
	public $is_error = false;

	/**
	 * Successful response flag (when HTTP response code is 200)
	 *
	 * @var boolean
	 */
	public $is_success = false;

	/**
	 * Init
	 *
	 * @param array $response (str, url, header)
	 * @param float $time_start
	 * @param resource $curl (optional, for cURL requests)
	 */
	public function __construct(array $response, $time_start, &$curl = null)
	{
		$this->__url = $response['url'];

		if(is_resource($curl)) // cURL request
		{
			$this->__str = @curl_exec($curl);
			$this->__time_elapsed = microtime(true) - $time_start;

			$header_len = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
			$headers = substr($this->__str, 0, $header_len);
			$this->__str = substr($this->__str, $header_len);

			$this->__headers = array_filter(explode("\r\n", $headers));

			if($this->__str === false)
			{
				$this->is_error = true;
				$this->error = curl_error($curl) . ' (Code: ' . curl_errno($curl) . ')';
			}

			// cleanup
			unset($headers);
			curl_close($curl);
		}
		else
		{
			$this->__str = $response['str'];
			$this->__time_elapsed = microtime(true) - $time_start;

			if(is_array($response['header']))
			{
				$this->__headers = &$response['header'];
			}

			if($this->__str === false) // error
			{
				$this->is_error = true;
				$this->error = error_get_last()['message'];
			}
		}

		foreach($this->__headers as $v)
		{
			if(preg_match('/HTTP\/\d\.\d (\d{3}).*?/i', $v, $m) && isset($m[1]))
			{
				$this->__response_code = (int)$m[1];
				break;
			}
		}

		if($this->__response_code === self::CODE_OK)
		{
			$this->is_success = true;
		}
	}

	/**
	 * Extract data from response string
	 *
	 * @param string $regex_pattern
	 * @param callable $array_map_callback (apply array_map to return array, callback must return value)
	 * @return array (matches)
	 */
	public function extract($regex_pattern, callable $array_map_callback = null)
	{
		if(@preg_match_all($regex_pattern, $this->__str, $m) === false) // error
		{
			throw new \InvalidArgumentException(__METHOD__ . ': invalid match regex pattern "'
				. $regex_pattern . '"');
		}
		else if(isset($m[0]))
		{
			return empty($array_map_callback) ? $m[0] : array_map($array_map_callback, $m[0]);
		}

		return [];
	}

	/**
	 * Request elapsed time in seconds getter
	 *
	 * @param int $precision
	 * @return float
	 */
	public function getElapsedTime($precision = 5)
	{
		return number_format($this->__time_elapsed, (int)$precision, '.', null);
	}

	/**
	 * Headers array getter
	 *
	 * @return array
	 */
	public function getHeaders()
	{
		return $this->__headers;
	}

	/**
	 * Get headers as objects
	 *
	 * @return \stdClass
	 */
	public function &getHeadersObject()
	{
		$headers = new \stdClass;

		foreach($this->__headers as $v)
		{
			if(preg_match('/^([\w\-]+)\:\s?(.*?)$/i', $v, $m) && isset($m[1], $m[2]))
			{
				$headers->{strtolower(preg_replace('/[^\w]/', '_', $m[1]))} = $m[2];
			}
		}

		return $headers;
	}

	/**
	 * Request URL getter
	 *
	 * @return string
	 */
	public function getRequestUrl()
	{
		return $this->__url;
	}

	/**
	 * HTTP response code getter
	 *
	 * @return int
	 */
	public function getResponseCode()
	{
		return $this->__response_code;
	}

	/**
	 * HTTP response string getter
	 *
	 * @return string
	 */
	public function getResponseString()
	{
		return $this->__str;
	}

	/**
	 * String/pattern match count in response string
	 *
	 * @param string $str_or_pattern (ex: as string 'keyword' or as pattern '/keyword/i')
	 * @return int (count of occurrences)
	 */
	public function match($str_or_pattern)
	{
		if(preg_match('/^\/.*\/[a-zA-Z]*?$/', $str_or_pattern)) // pattern
		{
			if(@preg_match_all($str_or_pattern, $this->__str, $m) === false) // error
			{
				throw new \InvalidArgumentException(__METHOD__ . ': invalid match regex pattern "'
					. $str_or_pattern . '"');
			}
			else if(isset($m[0]))
			{
				return count($m[0]);
			}
		}
		else // string
		{
			return substr_count($this->__str, $str_or_pattern);
		}

		return 0;
	}
}