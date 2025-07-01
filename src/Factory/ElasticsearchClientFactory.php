<?php
namespace App\Factory;

use Elasticsearch\ClientBuilder;

class ElasticsearchClientFactory
{

    public static function create(string $hosts)
    {
        return ClientBuilder::create()->setHosts(\explode(';', $hosts))->build();
    }
}

