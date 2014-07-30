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

/**
 * HTTP package example
 */

// load HTTP package files
require_once './lib/Http/Request.php';
require_once './lib/Http/Response.php';

try
{
	// create new HTTP request object
	$req = new \Http\Request('htp://www.example.com/');

	// set request params example:
	$req->param('var1', 'value_1');
	$req->param('var2', 'value_2');

	// set HTTP Response object with HTTP GET request
	$res = $req->get();

	if($res->is_success) // 200/OK
	{
		echo 'Response code: ' . $res->getResponseCode() . ', '; // ex: 200
		echo 'Response headers: ' . print_r($res->getHeaders(), true) . ', '; // array of headers
		echo 'Total seconds taken: ' . $res->getElapsedTime() . ', '; // total seconds for request
		echo 'Response string: ' . $res->getResponseString();
	}
	else if($res->is_error) // print error
	{
		echo 'Error: ' . $res->error;
	}
}
catch(\Exception $ex)
{
	echo 'Exception: ' . $ex->getMessage();
}