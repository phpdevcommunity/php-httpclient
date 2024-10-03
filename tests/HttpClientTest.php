<?php

namespace PhpDevCommunity\tests;

use Exception;
use LogicException;
use PhpDevCommunity\HttpClient\HttpClient;
use PHPUnit\Framework\TestCase;

class HttpClientTest extends TestCase
{
    const URL = 'http://localhost:4245';

    protected static ?string $serverProcess = null;

    public static function setUpBeforeClass(): void
    {
        $fileToRun = __DIR__ . DIRECTORY_SEPARATOR . 'test_server.php';
        $command = sprintf('php -S %s %s > /dev/null 2>&1 & echo $!;', str_replace('http://', '', self::URL), $fileToRun);
        self::$serverProcess = exec($command);
        if (empty(self::$serverProcess) || !is_numeric(self::$serverProcess)) {
            throw new Exception('Could not start test server');
        }
        sleep(1);
    }

    public function testGetRequest()
    {
        $response = http_client(
            ['base_url' => self::URL, 'headers' => ['Authorization' => 'Bearer secret_token']],
            function ($info) {
                $this->assertEquals( 'GET', $info['request']['method']);
                $this->assertEquals( 'Bearer secret_token', $info['request']['headers']['Authorization']);
                $this->assertEquals( '{"message":"GET request received"}', $info['response']['body']);
            }
        )->get('/api/data');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEmpty($response->getBody());
    }

    public function testGetWithQueryRequest()
    {
        $client = new HttpClient(['base_url' => self::URL, 'headers' => ['Authorization' => 'Bearer secret_token']]);
        $response = $client->get('/api/search', [
            'name' => 'foo',
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEmpty($response->getBody());

        $data = $response->bodyToArray();
        $this->assertEquals('foo', $data['name']);
        $this->assertEquals(1, $data['page']);
        $this->assertEquals(10, $data['limit']);


        $response = $client->get('/api/search', [
            'name' => 'foo',
            'page' => 10,
            'limit' => 100
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEmpty($response->getBody());

        $data = $response->bodyToArray();
        $this->assertEquals('foo', $data['name']);
        $this->assertEquals(10, $data['page']);
        $this->assertEquals(100, $data['limit']);
    }

    public function testPostJsonRequest()
    {
        $dataToPost = [
            'title' => 'foo',
            'body' => 'bar',
            'userId' => 1
        ];
        $client = new HttpClient(['headers' => ['Authorization' => 'Bearer secret_token']]);
        $response = $client->post(self::URL . '/api/post/data', [
            'title' => 'foo',
            'body' => 'bar',
            'userId' => 1
        ], true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($dataToPost, $response->bodyToArray());
    }

    public function testPostFormRequest()
    {
        $dataToPost = [
            'title' => 'foo',
            'body' => 'bar',
            'userId' => 1
        ];
        $client = new HttpClient(['headers' => ['Authorization' => 'Bearer secret_token']]);
        $response = $client->post(self::URL . '/api/post/data/form', $dataToPost);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($dataToPost, $response->bodyToArray());
    }

    public function testPostEmptyFormRequest()
    {
        $client = new HttpClient(['headers' => ['Authorization' => 'Bearer secret_token']]);
        $response = $client->post(self::URL . '/api/post/data/form', []);

        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testWrongOptions()
    {
        $this->expectException(LogicException::class);
        new HttpClient(['headers' => 'string']);
    }

    public function testWrongOptions2()
    {
        $this->expectException(LogicException::class);
        new HttpClient(['options_not_supported' => 'value']);
    }

    public function testWrongOptions3()
    {
        $this->expectException(LogicException::class);
        new HttpClient(['timeout' => 'string']);
    }

    public function testWrongMethod()
    {
        $client = new HttpClient(['headers' => ['Authorization' => 'Bearer secret_token']]);
        $this->expectException(LogicException::class);
        $client->fetch(self::URL . '/api/data', ['method' => 'WRONG']);
    }

    public function testWrongUrl()
    {
        $client = new HttpClient(['headers' => ['Authorization' => 'Bearer secret_token']]);
        $this->expectException(LogicException::class);
        $client->fetch('WRONG_URL', ['method' => 'GET']);
    }


    public static function tearDownAfterClass(): void
    {
        if (is_numeric(self::$serverProcess)) {
            exec('kill ' . self::$serverProcess);
        }
    }
}
