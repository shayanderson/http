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