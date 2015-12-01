<?php

namespace Atrapalo\Test\Monolog\Handler;

use Atrapalo\Monolog\Handler\ElasticsearchHandler;
use Elasticsearch\Client;
use PHPUnit_Framework_TestCase;
use Monolog\Logger;
use Prophecy\Argument;

class ElasticsearchHandlerTest extends PHPUnit_Framework_TestCase
{
    private $logger;

    /** @test */
    public function it_should_be_able_to_send_logs_to_an_elasticsearch_server()
    {
        $this->logger = new Logger('test');

        $elasticsearchClient = $this->prophesize(Client::class);

        $this->logger->pushHandler(
            new ElasticsearchHandler($elasticsearchClient->reveal())
        );

        $this->logger->info('This is a test');

        $elasticsearchClient->bulk(Argument::cetera())->shouldHaveBeenCalled();
    }
}