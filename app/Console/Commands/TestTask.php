<?php

namespace App\Console\Commands;

use App\Support\Client\EurekaClient;
use Illuminate\Console\Command;
use GuzzleHttp\Client;

class TestTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'eureka:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'eureka';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $client = EurekaClient::getInstance();
//        dd($client->register());
//        dd(dd($client->apps()));
        $baseUri = $client->getBaseUriByServiceName('laravel');
        if ($baseUri) {
            dump($baseUri);
            try {
                $httpClient = new Client([
                    'base_uri' => $baseUri,
                ]);
                $res = $httpClient->post('/');
                $json = json_decode($res->getBody()->getContents(), true);
                dd($json);
            } catch (\Exception $e) {
                dd($e->getMessage());
            }
        }
    }
}
