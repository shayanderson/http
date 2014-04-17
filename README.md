# HTTP Package
HTTP Request/Response Package for PHP 5.4+

## Quick Start
Use example.php to test:
```php
// load HTTP package files
require_once './lib/Http/Request.php';
require_once './lib/Http/Response.php';

// create new HTTP request object
$req = new \Http\Request('http://www.example.com/');

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
```

For a HTTP POST request example simply change the call method to:
```php
...
// set HTTP Response object with HTTP POST request
$res = $req->post();
...
```

For a HTTP HEAD request example simply change the call method to:
```php
...
// set HTTP Response object with HTTP HEAD request
$res = $req->head();
...
```

## HTTP Options
HTTP Request properties can be used to change HTTP request options

### Referer
A custom referer can be set using:
```php
$req->referer = 'http://www.example.com';
```

### Request Timeout
The request timeout (in seconds) can be modified using:
```php
$req->timeout_seconds = 10;
```

### Use cURL Library
By default the HTTP package uses the *file_get_contents()* function for HTTP requests, this can be change to use the cURL library using the option:
```php
$req->use_curl = true;
```

### User Agent
A custom user agent can be set using:
```php
$req->user_agent = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.8) Gecko/2009032609 Firefox/3.0.8';
```