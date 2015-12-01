<?php

namespace Atrapalo\Test\Monolog\Formatter;

use Atrapalo\Monolog\Formatter\ElasticsearchFormatter;
use DateTime;
use Monolog\Logger;
use PHPUnit_Framework_TestCase;

class ElasticsearchFormatterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ElasticsearchFormatter
     */
    private $elasticsearchFormatter;

    protected function setUp()
    {
        $this->elasticsearchFormatter = new ElasticsearchFormatter('index_name', 'type_name');
    }

    /**
     * @test
     */
    public function it_should_format_a_log_record_according_with_the_elasticsearch_client_format()
    {
        $record = [
            'level' => Logger::ERROR,
            'level_name' => 'ERROR',
            'channel' => 'meh',
            'context' => ['foo' => 7, 'bar', 'class' => new \stdClass],
            'datetime' => new DateTime("@0"),
            'extra' => [],
            'message' => 'log',
        ];

        $expected = [
            'index' => 'index_name',
            'type'  => 'type_name',
            'body' => $record
        ];

        $expected['body']['datetime'] = '1970-01-01T00:00:00+0000';
        $expected['body']['context'] = [
            'class' => '[object] (stdClass: {})',
            'foo' => 7,
            0 => 'bar',
        ];

        $this->assertEquals($expected, $this->elasticsearchFormatter->format($record));
    }

    /**
     * @test
     */
    public function it_should_format_a_batch_of_log_records_according_to_the_elasticsearch_client_format()
    {
        $batch = [
            [
                'level' => Logger::ERROR,
                'level_name' => 'ERROR',
                'channel' => 'meh',
                'context' => ['foo' => 7, 'bar', 'class' => new \stdClass],
                'datetime' => new DateTime("@0"),
                'extra' => [],
                'message' => 'log',
            ],
            [
                'level' => Logger::INFO,
                'level_name' => 'INFO',
                'channel' => 'hem',
                'context' => ['foo' => 8, 'bar', 'class' => new \stdClass],
                'datetime' => new DateTime("@0"),
                'extra' => [],
                'message' => 'log2',
            ],
        ];

        $expected = [
            'body' => [
                [
                    'index' => 'index_name',
                    'type'  => 'type_name'
                ],
                [
                    'level' => Logger::ERROR,
                    'level_name' => 'ERROR',
                    'channel' => 'meh',
                    'context' => ['class' => '[object] (stdClass: {})', 'foo' => 7, 0 => 'bar'],
                    'datetime' => '1970-01-01T00:00:00+0000',
                    'extra' => [],
                    'message' => 'log',
                ],
                [
                    'index' => 'index_name',
                    'type'  => 'type_name'
                ],
                [
                    'level' => Logger::INFO,
                    'level_name' => 'INFO',
                    'channel' => 'hem',
                    'context' => ['foo' => 8, 0 => 'bar', 'class' => '[object] (stdClass: {})'],
                    'datetime' => '1970-01-01T00:00:00+0000',
                    'extra' => [],
                    'message' => 'log2',
                ]
            ]
        ];

        $this->assertEquals($expected, $this->elasticsearchFormatter->formatBatch($batch));
    }
}