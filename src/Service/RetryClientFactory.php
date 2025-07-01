<?php
namespace App\Service;

use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class RetryClientFactory
{

    public static function buildClient($cfg)
    {
        $handlerStack = HandlerStack::create(new CurlHandler());
        $handlerStack->push(Middleware::retry('App\Service\RetryClientFactory::decider', 'App\Service\RetryClientFactory::delay'));

        $cfg['handler'] = $handlerStack;

        return new Client($cfg);
    }

    public static function decider($retries, Request $request, Response $response = null, GuzzleException $exception = null)
    {
        // Limit the number of retries to 5
        if ($retries >= 5) {
            return false;
        }

        // Retry connection exceptions
        if ($exception instanceof ConnectException) {
            return true;
        }

        if ($response) {
            // Retry on server errors
            if ($response->getStatusCode() >= 500) {
                return true;
            }
        }

        return false;
    }

    public static function delay($numberOfRetries)
    {
        return 1000 * $numberOfRetries;
    }
}

