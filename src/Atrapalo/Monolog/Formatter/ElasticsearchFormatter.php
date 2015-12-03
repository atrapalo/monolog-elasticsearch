<?php

namespace Atrapalo\Monolog\Formatter;

use DateTime;
use Monolog\Formatter\NormalizerFormatter;

class ElasticsearchFormatter extends NormalizerFormatter
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $index;

    public function __construct($index, $type)
    {
        parent::__construct(DateTime::ISO8601);

        $this->index = $index;
        $this->type = $type;
    }

    public function format(array $record)
    {
        $record = parent::format($record);

        return [
            'type'      => $this->type,
            'index'     => $this->index,
            'body'      => $record
        ];
    }

    public function formatBatch(array $records)
    {
        $bulk = ['body' => []];

        foreach ($records as $record) {
            $bulk['body'][] = [
                'index' => [
                    '_index' => $this->index,
                    '_type'  => $this->type,
                ]
            ];

            $bulk['body'][] = parent::format($record);
        }

        return $bulk;
    }
}