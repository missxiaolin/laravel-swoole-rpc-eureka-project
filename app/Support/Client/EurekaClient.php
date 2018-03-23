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

    /**
     * 发送
     * @return mixed
     */
    public function register()
    {
        $config = $this->config;
        $route = '/eureka/v2/apps/' . $config['appName'];
        $response = $this->client->post($route, [
            'body' => $this->getInstanceXml()
        ]);

        return $this->handleResponse($response);
    }

    /**
     * xml文件
     * @return mixed|string
     */
    protected function getInstanceXml()
    {
        $config = $this->config;

        $xml = file_get_contents(base_path() . '/config/eureka/instance.xml');
        $url = env('APP_URL');
        $appName = $config['appName'];

        $xml = str_replace('{{APP_NAME}}', $appName, $xml);
        $xml = str_replace('{{APP_URL}}', $url, $xml);
        return $xml;
    }
}