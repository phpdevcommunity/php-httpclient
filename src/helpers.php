<?php

use PhpDevCommunity\HttpClient\Http\Response;
use PhpDevCommunity\HttpClient\HttpClient;

if (!function_exists('http_client')) {

    /**
     * Creates a new HttpClient instance with the provided options.
     *
     * @param array $options The options to configure the HttpClient
     * @return HttpClient The newly created HttpClient instance
     */
    function http_client(array $options = [], callable $logger = null): HttpClient
    {
        return new HttpClient($options, $logger);
    }
}

if (!function_exists('http_post')) {
    /**
     * Makes a POST request using the HttpClient
     *
     * @param string $url The URL to which the request is sent
     * @param array $data The data to be sent in the request
     * @param array $headers The headers to be sent with the request
     * @return Response The response from the POST request
     */
    function http_post(string $url, array $data = [], array $headers = []): Response
    {
        return http_client()->post($url, $data, false, $headers);
    }
}

if (!function_exists('http_post_json')) {
    /**
     * Makes a POST request with JSON data using the HttpClient
     *
     * @param string $url The URL to which the request is sent
     * @param array $data The JSON data to be sent in the request
     * @param array $headers The headers to be sent with the request
     * @return Response The response from the POST request
     */
    function http_post_json(string $url, array $data = [], array $headers = []): Response
    {
        return http_client()->post($url, $data, true, $headers);
    }
}

if (!function_exists('http_get')) {
    /**
     * Makes a GET request using the HttpClient
     *
     * @param string $url The URL to which the request is sent
     * @param array $query The query parameters to be included in the request
     * @param array $headers The headers to be sent with the request
     * @return Response The response from the GET request
     */
    function http_get(string $url, array $query = [], array $headers = []): Response
    {
        return http_client()->get($url, $query, $headers);
    }
}



