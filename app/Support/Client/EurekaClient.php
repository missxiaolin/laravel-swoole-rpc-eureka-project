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
        'Accept' => 'application/json',
        'Content-Type' => 'application/xml',
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
            'headers' => $this->headers,
        ]);
    }

    protected function handleResponse(ResponseInterface $response)
    {
        $str = $response->getBody()->getContents();
        $xml = json_decode($str, true);
        return $xml;
    }

    public function apps()
    {
        $route = '/eureka/v2/apps';
        $response = $this->client->get($route);
        return $this->handleResponse($response);
    }

    public function register()
    {
        $config = $this->config;
        $route = '/eureka/v2/apps/' . $config['instance'];
        $xml = file_get_contents(base_path() . '/config/eureka/instance.xml');
        $response = $this->client->post($route, [
            'body' => $xml,
        ]);

        return $this->handleResponse($response);
    }
}