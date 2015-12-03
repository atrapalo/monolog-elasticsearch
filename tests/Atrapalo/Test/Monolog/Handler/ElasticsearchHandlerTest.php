<?php

namespace Atrapalo\Test\Monolog\Handler;

use Atrapalo\Monolog\Handler\ElasticsearchHandler;
use DateTime;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Elasticsearch\Common\Exceptions\NoNodesAvailableException;
use Monolog\Formatter\NormalizerFormatter;
use PHPUnit_Framework_TestCase;
use Monolog\Logger;
use Prophecy\Argument;

class ElasticsearchHandlerTest extends PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_should_be_able_to_send_logs_to_an_elasticsearch_server()
    {
        $logger = new Logger('test');

        $elasticsearchClient = $this->prophesize(Client::class);

        $logger->pushHandler(
            new ElasticsearchHandler($elasticsearchClient->reveal())
        );

        $logger->info('This is a test');

        $elasticsearchClient->index(Argument::type('array'))->shouldHaveBeenCalled();
    }

    /** @test */
    public function testIntegration()
    {
        $client =
            ClientBuilder::create()
                ->setHosts(['127.0.0.1:9200'])
            ->build()
        ;

        try {
            $client->ping();
        } catch (NoNodesAvailableException $e) {
            $this->markTestSkipped('Skipped due to a missing instance of Elasticsearch');
        }

        try {
            $client->indices()->delete(['index' => ElasticsearchHandler::DEFAULT_INDEX_NAME]);
        } catch (Missing404Exception $e) {
            // Noop
        }

        $logger = new Logger('application', [new ElasticsearchHandler($client)]);

        $timezone = new \DateTimeZone('UTC');
        $datetime =
            DateTime::createFromFormat('U.u', sprintf('%.6F', microtime(true)), $timezone)
                ->setTimezone($timezone)
                ->format(DateTime::ISO8601)
        ;

        $logger->setTimezone($timezone);
        $logger->info('This is a test!');

        sleep(1);

        $results = $client->search([
            'index' => ElasticsearchHandler::DEFAULT_INDEX_NAME,
            'type' => ElasticsearchHandler::DEFAULT_TYPE_NAME,
            'body' => [
                'query' => [
                    'match_all' => new \stdClass()
                ]
            ]
        ]);

        $this->assertGreaterThan(0, $results['hits']['total']);

        $expected = [
            'message' => 'This is a test!',
            'context' => [],
            'level' => 200,
            'level_name' => 'INFO',
            'channel' => 'application',
            'datetime' => $datetime,
            'extra' => []
        ];

        $this->assertEquals(
            $expected,
            $results['hits']['hits'][0]['_source']
        );
    }
}