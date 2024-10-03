# PHP Http Client

This PHP HTTP Client provides a minimalistic way to perform `GET` and `POST` HTTP requests with customizable headers. It allows you to handle JSON data, form-encoded requests, and more, without using cURL.

## Installation

You can install this library via [Composer](https://getcomposer.org/). Ensure your project meets the minimum PHP version requirement of 7.4.

```bash
composer require phpdevcommunity/php-httpclient
```
## Requirements

- PHP version 7.4 or higher

## Features

- Supports `GET` and `POST` requests.
- Customize headers for each request.
- Automatically handles JSON or form-encoded data.
- Easily configurable base URL for all requests.
- Includes error handling for invalid URLs and timeouts.

## Usage

### Basic GET Request

```php
use PhpDevCommunity\HttpClient\HttpClient;

$client = new HttpClient(['base_url' => 'http://example.com']);

// Perform a GET request
$response = $client->get('/api/data');

if ($response->getStatusCode() === 200) {
    echo $response->getBody(); // Raw response body
    print_r($response->bodyToArray()); // JSON decoded response
}
```

### GET Request with Query Parameters

```php
$response = $client->get('/api/search', ['query' => 'test']);
```

### POST Request (Form-Encoded)

```php
$data = [
    'username' => 'testuser',
    'password' => 'secret'
];

$response = $client->post('/api/login', $data);
```

### POST Request (JSON)

```php
$data = [
    'title' => 'Hello World',
    'content' => 'This is a post content'
];

$response = $client->post('/api/posts', $data, true); // `true` specifies JSON content type
```

### Custom Headers

```php
$client = new HttpClient([
    'base_url' => 'http://example.com',
    'headers' => ['Authorization' => 'Bearer your_token']
]);

$response = $client->get('/api/protected');
```

---

## Helper Functions

To make the HTTP client easier to use, we provide a set of helper functions that allow you to quickly send `GET` and `POST` requests without needing to manually instantiate the `HttpClient` class every time.

### Available Helper Functions

#### 1. `http_client()`

This function creates and returns a new `HttpClient` instance with the provided configuration options.

```php
$client = http_client([
    'base_url' => 'http://example.com',
    'headers' => ['Authorization' => 'Bearer your_token']
]);
```

#### 2. `http_post()`

Use this function to make a POST request with form-encoded data. It sends a request to the given URL with optional data and headers.

```php
$response = http_post('http://example.com/api/login', [
    'username' => 'user123',
    'password' => 'secret'
]);
```

#### 3. `http_post_json()`

This function sends a POST request with JSON-encoded data. Useful for APIs expecting JSON input.

```php
$response = http_post_json('http://example.com/api/create', [
    'title' => 'New Post',
    'body' => 'This is the content of the new post.'
]);
```

#### 4. `http_get()`

Make a GET request using this function. You can include query parameters and headers as needed.

```php
$response = http_get('http://example.com/api/users', [
    'page' => 1,
    'limit' => 10
]);
```

### Example Usage of Helpers

```php
// Make a GET request
$response = http_get('http://api.example.com/items', ['category' => 'books']);
$data = $response->bodyToArray();

// Make a POST request with form data
$response = http_post('http://api.example.com/login', [
    'username' => 'user123',
    'password' => 'secret'
]);

// Make a POST request with JSON data
$response = http_post_json('http://api.example.com/posts', [
    'title' => 'Hello World',
    'content' => 'This is my first post!'
]);
```

These helper functions simplify making HTTP requests by reducing the need to manually create and configure the `HttpClient` for each request.



