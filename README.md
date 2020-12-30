# php-curl

Simple CURL wrapper library for PHP.

Includes PSR-18 compliant HTTP client.

[![Build Status](https://travis-ci.org/fm-labs/php-curl.svg?branch=main)](https://travis-ci.org/fm-labs/php-uri)

## Requirements

- php 7.1+

## Installation

```console
$ composer require fm-labs/php-curl
```

## Usage

## Tests
```console
$ composer run-script test
// or
$ composer run-script test-verbose
// or
$ ./vendor/bin/phpunit --bootstrap tests/bootstrap.php tests/
```

## TODO


## Changelog
[1.0]
- Added: Curl wrapper class
- Added: Curl class supports PSR-3 compliant LoggerInterface 
- Added: CurlClient class (http client)
- Added: CurlClient is complaint to PSR-18 HttpClientInterface
- Added: CurlResponse class

## License

See LICENSE file



