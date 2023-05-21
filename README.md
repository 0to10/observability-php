[![Maintainability Rating](https://sonarcloud.io/api/project_badges/measure?project=0to10_observability-php&metric=sqale_rating)](https://sonarcloud.io/summary/new_code?id=0to10_observability-php)
[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=0to10_observability-php&metric=alert_status)](https://sonarcloud.io/summary/new_code?id=0to10_observability-php)
[![Code Smells](https://sonarcloud.io/api/project_badges/measure?project=0to10_observability-php&metric=code_smells)](https://sonarcloud.io/summary/new_code?id=0to10_observability-php)
[![Coverage](https://sonarcloud.io/api/project_badges/measure?project=0to10_observability-php&metric=coverage)](https://sonarcloud.io/summary/new_code?id=0to10_observability-php)


[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/0to10/observability-php/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/0to10/observability-php/?branch=main)
[![Build Status](https://scrutinizer-ci.com/g/0to10/observability-php/badges/build.png?b=main)](https://scrutinizer-ci.com/g/0to10/observability-php/build-status/main)
[![Code Coverage](https://scrutinizer-ci.com/g/0to10/observability-php/badges/coverage.png?b=main)](https://scrutinizer-ci.com/g/0to10/observability-php/?branch=main)


# Observability library for PHP

This package aims to make it easier to monitor your application by
generalising frequently used methods to customise how you instrument
your code.


## Getting started

Getting started is usually easy: just follow the instructions below.


### Installation

Use [Composer](https://getcomposer.org/) to install this library into your project:

```shell
composer require 0to10/observability-php
```


### Basic usage

Below is the high-level documentation of how to work with this library.

```php
<?php

require 'vendor/autoload.php';

use ZERO2TEN\Observability\Client;
use ZERO2TEN\Observability\APM\Agent\NullAgent;

$nullAgent = new NullAgent();
$client = new Client($nullAgent);

try {
    // Some application code
} catch (\Exception $e) {
    $client->transaction()->recordException($e);
}

// Add a parameter to the current transaction
$client->transaction()->addParameter('user_id', 50);
```


### Working with transactions

A _Transaction_ is a logical unit of work in a software application. This might
be handling a request and sending a response (in a Web transaction), executing
a script that handles some piece of business logic, etc.

This library exposes a helper class via the `transaction()` method of a
`Client` instance that can be used to customise monitoring of a transaction.

The methods of the returned class are documented in `TransactionInterface`.


### Real-user monitoring

Real-user monitoring (RUM) may be customised by calling the `browser()` method
of a `Client` instance.

Automatic instrumentation (e.g. automatic injection of browser scripts in the
header and footer of a page) may be disabled using the following method:

```php
<?php

require 'vendor/autoload.php';

use ZERO2TEN\Observability\Client;
use ZERO2TEN\Observability\APM\Agent\NullAgent;

$client = new Client(new NullAgent);

$browser = $client->browser();

$browser->disableAutomaticTimingScripts();
```

Note that this must be done before any output is sent to the browser, and that
it's more stable to disable RUM as part of your PHP configuration (if available).

```php
<?php

require 'vendor/autoload.php';

use ZERO2TEN\Observability\Client;
use ZERO2TEN\Observability\APM\Agent\NullAgent;

$client = new Client(new NullAgent);

$browser = $client->browser();

// Returns the header RUM script (Javascript) as string without <script> tags
$browser->getHeaderScript();

// Returns the footer RUM script (Javascript) as string without <script> tags
$browser->getFooterScript();
```


### Legacy usage

Library versions up to 2.0.0 continue to support using the `Client` class located
in the `Nouve\APM` namespace. Please do not use this method for new projects.

```php
<?php

require 'vendor/autoload.php';

use Nouve\APM\Agents\NullAgent;
use Nouve\APM\Client;

// Creates a Client instance with the first supported AgentInterface
// implementation configured in the Client class
$client = Client::create();

// Alternatively, you can instantiate a Client instance yourself
$nullAgent = new NullAgent();
$client = new Client($nullAgent);
```


## Usage notes

It is important to understand that this library exposes generic methods to
adjust observability within projects. Depending on the capabilities of the used
observability tool, methods may not have the desired outcome. Always make sure
that you understand the impact of customising your setup before publishing this
into any production environment.
