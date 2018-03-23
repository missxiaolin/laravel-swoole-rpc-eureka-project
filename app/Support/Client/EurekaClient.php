<?php
namespace App\Support\Client;

use Exception;
use Illuminate\Support\Arr;
use App\Support\Enums\ErrorCode;
use App\Support\InstanceTrait;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Illuminate\Support\Facades\Redis;

class EurekaClient
{
    use InstanceTrait;

    protected $client;

    protected $config;

    protected $url;

    protected $port;

    protected $headers = [
        'Accept' => 'application/json',
        'Content-Type' => 'application/xml',
    ];

    /**
     * 初始化
     * EurekaClient constructor.
     * @throws Exception
     */
    public function __construct()
    {
        $config = config('eureka')['eureka'];
        $this->url = env('APP_URL');
        $this->port = env('APP_PORT');
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

    /**
     * @param ResponseInterface $response
     * @return mixed
     */
    protected function handleResponse(ResponseInterface $response)
    {
        $str = $response->getBody()->getContents();
        $xml = json_decode($str, true);
        return $xml;
    }

    /**
     * @return mixed
     */
    public function apps()
    {
        $route = '/eureka/v2/apps';
        $response = $this->client->get($route);
        return $this->handleResponse($response);
    }

    /**
     * @param $appName
     * @return mixed
     */
    public function app($appName)
    {
        $route = '/eureka/v2/apps/' . $appName;
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
            'body' => $this->getInstanceXml(),
        ]);

        return $this->handleResponse($response);
    }

    /**
     * @return mixed
     */
    public function heartbeat()
    {
        $config = $this->config;
        $appName = $config['appName'];
        $url = $this->url;
        $route = sprintf('/eureka/v2/apps/%s/%s', $appName, $url);
        $response = $this->client->put($route);

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
        $appName = $config['appName'];

        $xml = str_replace('{{APP_NAME}}', $appName, $xml);
        $xml = str_replace('{{APP_URL}}', $this->url, $xml);
        $xml = str_replace('{{APP_PORT}}', $this->port, $xml);
        return $xml;
    }

    /**
     * @param $serviceName
     * @return mixed
     */
    public function getBaseUriByServiceName($serviceName)
    {
        $config = $this->config;
        $redisKey = sprintf($config['cacheKeyPrefix'], ucwords(strtolower($serviceName)));
        return Redis::sRandMember($redisKey);
    }

    public function cacheServices()
    {
        $apps = Arr::get($this->apps(), 'applications', []);
        if (!isset($apps['application'])) {
            // 不存在服务
            return;
        }

        $apps = $apps['application'];
        if (isset($apps['name'])) {
            $apps = [$apps];
        }

        foreach ($apps as $app) {
            $this->cacheSingleService($app['instance'], $app['name']);
        }
    }

    /**
     * @param $services
     * @param $name
     */
    protected function cacheSingleService($services, $name)
    {
        $config = $this->config;
        $redisKey = sprintf($config['cacheKeyPrefix'], ucwords(strtolower($name)));
        if (isset($services['app'])) {
            $services = [$services];
        }

        foreach ($services as $service) {
            // 只存在一个实例
            $port = $service['port']['$'];
            if ($port != 80) {
                $item = 'http://' . $service['ipAddr'] . ':' . $port . '/';
            } else {
                $item = 'http://' . $service['ipAddr'] . '/';
            }
            Redis::sadd($redisKey, $item);
            Redis::expire($redisKey, 60);
        }
    }
}