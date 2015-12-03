Monolog Elasticsearch handler and formatter
===========================================

This extremely simple library provide of an elasticsearch handler and formatter which makes use of the official PHP
Elasticsearch client.

[![Build Status](https://travis-ci.org/atrapalo/monolog-elasticsearch.svg?branch=master)](https://travis-ci.org/atrapalo/monolog-elasticsearch) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/atrapalo/monolog-elasticsearch/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/atrapalo/monolog-elasticsearch/?branch=master)

## Usage

```php
<?php

use Atrapalo\Monolog\Handler\ElasticsearchHandler;
use Elasticsearch\ClientBuilder;
use Monolog\Logger;

$logger = new Logger('application');
$logger->pushHandler(
    new ElasticsearchHanler($client, ['index' => 'logs', 'type' => 'log'])
);

```

## Installation

This library can be installed through composer

```sh
composer require atrapalo/monolog-elasticsearch
``` 

## Requirements

In order to make use of this library you will need

* Monolog
* An elasticsearch instance

## Contributing

See CONTRIBUTING file.

## Running the Tests

```bash
php bin/phpunit
```

## Credits

* Christian Soronellas <christian.soronellas@atrapalo.com>

## Contributor Code of Conduct

Please note that this project is released with a [Contributor Code of Conduct](http://contributor-covenant.org/). By 
participating in this project you agree to abide by its terms. See [CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md) file.

## License

Monolog-Elasticsearch handler is released under the MIT License. See the bundled LICENSE file for details.
