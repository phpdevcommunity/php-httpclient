<?php

namespace PhpDevCommunity\HttpClient;

use InvalidArgumentException;
use LogicException;
use PhpDevCommunity\HttpClient\Http\Response;

final class HttpClient
{
    /**
     * The options for configuring the HttpClient.
     *
     * @var array
     *
     * Possible options:
     * - string user_agent The user agent to use for the request.
     * - int timeout The timeout value in seconds for the request.
     * - array headers An associative array of HTTP headers to include in the request.
     * - string base_url The base URL to prepend to relative URLs in the request.
     */
    private array $options;

    /**
     * HttpClient constructor.
     *
     * @param array $options An array of options for HttpClient.
     *                      Possible options:
     *                      - string user_agent The user agent to use for the request.
     *                      - int timeout The timeout value in seconds for the request.
     *                      - array headers An associative array of HTTP headers to include in the request.
     *                      - string base_url The base URL to prepend to relative URLs in the request.
     */
    public function __construct(array $options = [])
    {
        self::validateOptions($options, ['user_agent', 'timeout', 'headers', 'base_url']);
        $this->options = array_replace([
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.90 Safari/537.36',
            'timeout' => 30,
            'headers' => [],
            'base_url' => null,
        ], $options);
    }

    /**
     * Perform a GET request.
     *
     * @param string $url The URL to send the GET request to.
     * @param array $query An associative array of query parameters.
     * @param array $headers An associative array of HTTP headers to include in the request.
     * @return Response The response object.
     */
    public function get(string $url, array $query = [], array $headers = []): Response
    {
        $options['headers'] = $headers;
        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }
        return $this->fetch($url, $options);
    }

    /**
     * Perform a POST request.
     *
     * @param string $url The URL to send the POST request to.
     * @param array $data An associative array of data to be sent in the request body.
     * @param bool $json Whether to send the data as JSON.
     * @param array $headers An associative array of HTTP headers to include in the request.
     * @return Response The response object.
     */
    public function post(string $url, array $data, bool $json = false, array $headers = []): Response
    {
        $options['method'] = 'POST';
        $options['body'] = $data;
        $options['headers'] = $headers;
        if ($json) {
            $options['headers']['Content-Type'] = 'application/json';
        }
        return $this->fetch($url, $options);
    }

    /**
     * Perform a fetch request.
     *
     * @param string $url The URL to fetch.
     * @param array $options An associative array of options for the fetch request.
     *                      Possible options:
     *                      - string user_agent The user agent to use for the request.
     *                      - int timeout The timeout value in seconds for the request.
     *                      - array headers An associative array of HTTP headers to include in the request.
     *                      - string base_url The base URL to prepend to relative URLs in the request.
     *                      - string body The body of the request.
     *                      - string method The HTTP method to use for the request.
     * @return Response The response object.
     */
    public function fetch(string $url, array $options = []): Response
    {
        self::validateOptions($options, ['user_agent', 'timeout', 'headers', 'body', 'method']);

        $options = array_merge_recursive($this->options, $options);
        $context = $this->createContext($options);

        if (!empty($options['base_url'])) {
            $baseUrl = rtrim($options['base_url'], '/') . '/';
            $url = ltrim($url, '/');
            $url = $baseUrl . $url;
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException(sprintf('Invalid URL: %s', $url));
        }

        $response = '';
        $fp = fopen($url, 'rb', false, $context);
        $httpResponseHeaders = $http_response_header;
        if ($fp === false) {
            $detail = $httpResponseHeaders[0] ?? '';
            throw new LogicException(sprintf('Error opening request to %s: %s', $url, $detail));
        }

        while (!feof($fp)) {
            $response .= fread($fp, 8192);
        }

        fclose($fp);

        $headers = self::parseHttpResponseHeaders($httpResponseHeaders);

        return new Response($response, $headers['status_code'] ?? HttpStatusCode::HTTP_VERSION_NOT_SUPPORTED, $headers);
    }

    /**
     * Create a stream context based on the provided options.
     *
     * @param array $options An associative array of options for creating the stream context.
     * @return resource The created stream context.
     */
    private function createContext(array $options)
    {
        $method = strtoupper($options['method'] ?? 'GET');
        $body = $options['body'] ?? '';

        if (in_array($method, ['POST', 'PUT']) && is_array($body)) {
            $body = self::prepareRequestBody($body, $options['headers']);
        }

        $opts = [
            'http' => [
                'method' => $method,
                'header' => self::formatHttpRequestHeaders($options['headers']),
                'content' => $body,
                'user_agent' => $options['user_agent'],
                'ignore_errors' => true,
                'timeout' => $options['timeout']
            ]
        ];

        return stream_context_create($opts);
    }

    /**
     * Format HTTP headers from the provided associative array.
     *
     * @param array $headers An associative array of HTTP headers.
     * @return string The formatted HTTP headers.
     */
    private static function formatHttpRequestHeaders(array $headers): string
    {
        $formattedHeaders = '';
        foreach ($headers as $name => $value) {
            $formattedHeaders .= "$name: $value\r\n";
        }
        return $formattedHeaders;
    }

    /**
     * Prepare the request body based on content type.
     *
     * @param array $body The body of the request.
     * @param array $headers The headers to be sent with the request.
     * @return string The prepared request body.
     */
    private static function prepareRequestBody(array $body, array &$headers): string
    {
        if (($headers['Content-Type'] ?? '') === 'application/json') {
            return json_encode($body);
        }

        $headers['Content-Type'] = 'application/x-www-form-urlencoded';
        return http_build_query($body);
    }

    /**
     * Parse the HTTP response headers into an associative array.
     *
     * @param array $responseHeaders The headers from the HTTP response.
     * @return array The parsed response headers.
     */
    private static function parseHttpResponseHeaders(array $responseHeaders): array
    {
        $headers = [];
        foreach ($responseHeaders as $header) {
            $headerParts = explode(':', $header, 2);
            if (count($headerParts) == 2) {
                $key = trim($headerParts[0]);
                $value = trim($headerParts[1]);
                $headers[$key] = $value;
            } else {
                if (preg_match('{HTTP/\S*\s(\d{3})}', $header, $match)) {
                    $httpCode = (int)$match[1];
                    $headers['status_code'] = $httpCode;
                }
            }
        }
        return $headers;
    }

    /**
     * Validate the options passed for the HTTP request.
     *
     * @param array $options An associative array of options for the HTTP request.
     * @param array $allowedOptions An array of allowed options.
     * @throws LogicException If any of the options are invalid.
     */
    private static function validateOptions(array $options, array $allowedOptions = []): void
    {
        foreach ($options as $key => $value) {
            if (!in_array($key, $allowedOptions)) {
                throw new LogicException('Invalid option: ' . $key);
            }

            switch ($key) {
                case 'headers':
                    if (!is_array($value)) {
                        throw new LogicException('Headers must be an array of key-value pairs');
                    }
                    break;
                case 'user_agent':
                    if (!is_string($value)) {
                        throw new LogicException('User agent must be a string');
                    }
                    break;
                case 'timeout':
                    if (!is_int($value)) {
                        throw new LogicException('Timeout must be an integer');
                    }
                    break;
                case 'method':
                    if (!is_string($value) || !in_array($value, ['GET', 'POST', 'PUT', 'DELETE', 'HEAD'])) {
                        throw new LogicException('Method must be GET, POST, PUT, DELETE, or HEAD');
                    }
                    break;
                case 'base_url':
                    if (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
                        throw new LogicException('Base URL must be a valid URL');
                    }
                    break;
            }
        }
    }
}
