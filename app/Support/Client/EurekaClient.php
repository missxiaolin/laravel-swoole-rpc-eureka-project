<?php
namespace App\Support\Client;

use Exception;
use App\Support\Enums\ErrorCode;
use App\Support\InstanceTrait;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

class EurekaClient
{
    use InstanceTrait;

    protected $client;

    protected $config;

    protected $headers = [
        'Content-Type' => 'application/json',
        'Accept' => 'application/json'
    ];

    public function __construct()
    {
        $config = config('eureka')['eureka'];
        $this->config = $config;
        $baseUri = $config['baseUri'];

        if (empty($baseUri)) {
            throw new Exception(ErrorCode::$ENUM_EUREKA_CONFIG_INVALID);
        }

        $this->client = new Client([
            'base_uri' => $baseUri,
        ]);
    }

    protected function handleResponse(ResponseInterface $response)
    {
        $str = $response->getBody()->getContents();
        return $str;
        $xml = simplexml_load_string($str);
        return $xml;
    }

    public function apps()
    {
        $route = '/eureka/v2/apps';
        $response = $this->client->get($route, [
            'headers' => $this->headers
        ]);
        return $this->handleResponse($response);
    }

    public function register()
    {
        $config = $this->config;
        $route = '/eureka/v2/apps/' . $config['instance'];
        $response = $this->client->post($route, [
            'json' => [
                'instance' => [
                    'hostName' => '127.0.0.1',
                    'app' => 'xxx',
                    'vipAddress' => 'xxx',
                    'secureVipAddress' => 'xxx',
                    'ipAddr' => 'ss',
                    'status' => 'UP',
                    'port' => '80',
                    'securePort' => '443',
                ],
            ],
        ]);

        return $this->handleResponse($response);
    }
}