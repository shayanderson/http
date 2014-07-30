<?php
/**
 * HTTP Request/Response Package for PHP 5.4+
 *
 * @package HTTP
 * @version 1.0
 * @copyright 2014 Shay Anderson <http://www.shayanderson.com>
 * @license MIT License <http://www.opensource.org/licenses/mit-license.php>
 * @link <https://github.com/shayanderson/http>
 */
namespace Http;

use Http\Response;

/**
 * HTTP Request class
 *
 * @author Shay Anderson 04.14 <http://www.shayanderson.com/contact>
 */
class Request
{
	/**
	 * Request method types
	 */
	const METHOD_GET = 'GET';
	const METHOD_HEAD = 'HEAD';
	const METHOD_POST = 'POST';

	/**
	 * Request params
	 *
	 * @var array
	 */
	private $__params = [];

	/**
	 * Request URL
	 *
	 * @var string
	 */
	private $__url;

	/**
	 * Follow redirects/locations (ex: 301)
	 *
	 * @var boolean
	 */
	public $follow_redirects = true;

	/**
	 * Request referer
	 *
	 * @var string
	 */
	public $referer;

	/**
	 * Request timeout in seconds
	 *
	 * @var int
	 */
	public $timeout_seconds = 10;

	/**
	 * Use cURL connection flag (instead of file_get_contents())
	 *
	 * @var boolean
	 */
	public $use_curl = false;

	/**
	 * Request user agent, examples:
	 *		'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.8) Gecko/2009032609 Firefox/3.0.8'
	 *		'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:28.0) Gecko/20100101 Firefox/28.0'
	 *		'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.116 Safari/537.36'
	 *		'Mozilla/5.0 (Windows NT 6.3; WOW64; Trident/7.0; rv:11.0) like Gecko'
	 *
	 * @var string
	 */
	public $user_agent;

	/**
	 * Init URL
	 *
	 * @param string $url
	 * @throws \InvalidArgumentException
	 */
	public function __construct($url)
	{
		if(empty($url)
			|| !preg_match('/(https?:\/\/(\S*?\.\S*?))(\s|\;|\)|\]|\[|\{|\}|,|\"|\'|:|\<|$|\.\s)/i', $url))
		{
			throw new \InvalidArgumentException(__METHOD__ . ': invalid URL "' . $url . '"');
		}

		$this->__url = $url;
	}

	/**
	 * Get HTTP response
	 *
	 * @param string $method
	 * @return \Http\Response
	 */
	private function __getHttpResponse($method)
	{
		if($this->use_curl) // use cURL
		{
			if(!function_exists('curl_init')) // ensure cURL support
			{
				throw new \BadFunctionCallException(__METHOD__
					. ': function curl_init not found, install cURL library');
			}
			else
			{
				$curl = curl_init();

				$request_url = $this->__url;

				if($method !== self::METHOD_POST)
				{
					$request_url .= '?' . http_build_query($this->__params);
				}

				curl_setopt_array($curl, [
					CURLOPT_RETURNTRANSFER => 1,
					CURLOPT_HEADER => 1,
					CURLOPT_URL => $request_url,
					CURLOPT_TIMEOUT => $this->timeout_seconds,
					CURLOPT_CONNECTTIMEOUT => $this->timeout_seconds
				]);

				if($this->follow_redirects)
				{
					curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
				}

				if($method === self::METHOD_HEAD)
				{
					curl_setopt($curl, CURLOPT_NOBODY, 1);
					curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 0);
				}
				else if($method === self::METHOD_POST)
				{
					curl_setopt($curl, CURLOPT_POST, 1);

					if(count($this->__params) > 0)
					{
						curl_setopt($curl, CURLOPT_POSTFIELDS, $this->__params);
					}
				}

				if(!empty($this->referer))
				{
					curl_setopt($curl, CURLOPT_REFERER, $this->referer);
				}

				if(!empty($this->user_agent))
				{
					curl_setopt($curl, CURLOPT_USERAGENT, $this->user_agent);
				}

				return new Response(['url' => $request_url], microtime(true), $curl);
			}
		}
		else
		{
			$params = [
				'http' => [
					'method' => $method,
					'timeout' => $this->__getTimeout()
				]
			];

			if($this->follow_redirects)
			{
				$params['http']['follow_location'] = true;
			}

			if($method === self::METHOD_HEAD)
			{
				$params['http']['follow_location'] = false; // do not follow for HEAD request
			}

			if($method === self::METHOD_POST)
			{
				$params['http']['header'] = 'Content-type: application/x-www-form-urlencoded' . "\r\n";
			}

			if(!empty($this->referer))
			{
				$params['http']['header'] .= 'Referer: ' . $this->referer . "\r\n";
			}

			if(!empty($this->user_agent))
			{
				$params['http']['user_agent'] = $this->user_agent;
			}

			$request_url = $this->__url;

			if(count($this->__params) > 0)
			{
				if($method === self::METHOD_POST)
				{
					$params['http']['content'] = http_build_query($this->__params);
				}
				else // params as query string
				{
					$request_url .= '?' . http_build_query($this->__params);
				}
			}

			$c = stream_context_create($params);

			$http_response_header = null;

			$time_start = microtime(true);

			return new Response([
				'str' => @file_get_contents($request_url, false, $c),
				'url' => $request_url,
				'header' => $http_response_header
			], $time_start);
		}
	}

	/**
	 * Timeout getter
	 *
	 * @return int
	 */
	private function __getTimeout()
	{
		return (int)$this->timeout_seconds > 0 ? (int)$this->timeout_seconds : 10;
	}

	/**
	 * HTTP GET request method
	 *
	 * @return \Http\Response
	 */
	public function get()
	{
		return $this->__getHttpResponse(self::METHOD_GET);
	}

	/**
	 * HTTP HEAD request method
	 *
	 * @return \Http\Response
	 */
	public function head()
	{
		return $this->__getHttpResponse(self::METHOD_HEAD);
	}

	/**
	 * Add request param key/value
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public function param($key, $value = null)
	{
		$this->__params[$key] = $value;
	}

	/**
	 * HTTP POST request method
	 *
	 * @return \Http\Response
	 */
	public function post()
	{
		return $this->__getHttpResponse(self::METHOD_POST);
	}
}