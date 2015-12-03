<?php

namespace Atrapalo\Monolog\Handler;

use Atrapalo\Monolog\Formatter\ElasticsearchFormatter;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Formatter\FormatterInterface;
use InvalidArgumentException;
use Elasticsearch\Client;
use Monolog\Logger;

/**
 * Elasticsearch handler
 *
 * Usage example:
 *
 *    $client = ClientBuilder::create()->build();
 *    $options = [
 *        'index' => 'elastic_index_name',
 *        'type' => 'elastic_doc_type',
 *    ];
 *    $handler = new ElasticsearchHandler($client, $options);
 *    $log = new Logger('application');
 *    $log->pushHandler($handler);
 *
 * @author Christian Soronellas <christian.soronellas@atrapalo.com>
 */
class ElasticsearchHandler extends AbstractProcessingHandler
{
    const DEFAULT_INDEX_NAME = 'monolog';
    const DEFAULT_TYPE_NAME  = 'record';

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var array Handler config options
     */
    protected $options = [];

    /**
     * @param Client  $client  Elastica Client object
     * @param array   $options Handler configuration
     * @param integer $level   The minimum logging level at which this handler will be triggered
     * @param Boolean $bubble  Whether the messages that are handled can bubble up the stack or not
     */
    public function __construct(Client $client, array $options = [], $level = Logger::DEBUG, $bubble = true)
    {
        $this->client = $client;
        $this->options = array_merge(['index' => static::DEFAULT_INDEX_NAME, 'type' => static::DEFAULT_TYPE_NAME], $options);

        parent::__construct($level, $bubble);
    }

    /**
     * {@inheritDoc}
     */
    protected function write(array $record)
    {
        $this->client->index($record['formatted']);
    }

    /**
     * {@inheritdoc}
     */
    public function setFormatter(FormatterInterface $formatter)
    {
        if ($formatter instanceof ElasticsearchFormatter) {
            return parent::setFormatter($formatter);
        }

        throw new InvalidArgumentException('ElasticsearchHandler is only compatible with ElasticsearchFormatter');
    }

    /**
     * Getter options
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultFormatter()
    {
        return new ElasticsearchFormatter($this->options['index'], $this->options['type']);
    }

    /**
     * {@inheritdoc}
     */
    public function handleBatch(array $records)
    {
        $this->client->bulk(
            $this->getFormatter()->formatBatch($records)
        );
    }
}
